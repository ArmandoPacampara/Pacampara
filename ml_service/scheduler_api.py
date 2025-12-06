import pandas as pd
from sqlalchemy import create_engine, text
from sklearn.ensemble import RandomForestRegressor
from flask import Flask, jsonify, request
import requests
from datetime import datetime, timedelta

app = Flask(__name__)

# --- CONFIGURATION ---
DB_URI = "mysql+mysqlconnector://root:admin@localhost:3307/moviease_db"

# --- OPTIMIZATION #2: GLOBAL DATABASE ENGINE ---
# Creating the engine once globally enables connection pooling.
# pool_size=10: Maintains 10 open connections ready to use.
# pool_recycle=3600: Refreshes connections every hour to prevent timeouts.
engine = create_engine(DB_URI, pool_size=10, pool_recycle=3600)

# --- OPTIMIZATION #1: GLOBAL MODEL CACHING ---
# We store the trained model here so we don't retrain it on every single user request.
trained_model = None

def get_training_data():
    """Fetches historical booking data using the global engine."""
    try:
        # Connect using the global engine pool
        with engine.connect() as conn:
            query = """
            SELECT schedule, COUNT(ticket_id) as attendance 
            FROM booking 
            WHERE status = 'Completed' 
            GROUP BY schedule
            """
            df = pd.read_sql(query, conn)
        
        if df.empty:
            # Fallback data if database is empty to prevent crashes
            data = {
                'schedule': pd.date_range(start='2024-01-01', periods=100, freq='H'),
                'attendance': [5, 10, 20, 50, 5, 2, 15, 45] * 12 + [10, 10, 10, 10]
            }
            df = pd.DataFrame(data)
            
    except Exception as e:
        print(f"Database Error (Training): {e}")
        return pd.DataFrame()
    
    if not df.empty:
        df['schedule'] = pd.to_datetime(df['schedule'])
    return df

def get_or_train_model():
    """
    Returns the cached model if it exists. 
    Otherwise, trains the model and caches it.
    """
    global trained_model
    
    # If we already have a model, return it immediately (0s latency)
    if trained_model is not None:
        return trained_model

    print("Training Model (This happens only once)...")
    df = get_training_data()
    
    if df.empty:
        return None

    # Feature Engineering
    df['day_of_week'] = df['schedule'].dt.dayofweek
    df['hour'] = df['schedule'].dt.hour
    
    X = df[['day_of_week', 'hour']]
    y = df['attendance']
    
    # Train Model
    model = RandomForestRegressor(n_estimators=100, random_state=42)
    model.fit(X, y)
    
    # Save to global variable
    trained_model = model
    return trained_model

@app.route('/predict_best_time', methods=['POST'])
def predict_best_time():
    data = request.json
    movie_id = data.get('movie_id')

    if not movie_id:
        return jsonify({'error': 'Movie ID required'}), 400

    # --- GET MODEL (CACHED) ---
    model = get_or_train_model()
    if not model:
        return jsonify({'error': 'Not enough data to predict'}), 500

    try:
        # Fetch showtimes for the next 7 days for this movie
        with engine.connect() as conn:
            query = text("""
                SELECT cm.showtime, c.name as cinema_name, m.title as movie_name, 
                       m.poster as movie_poster, m.movie_id, cm.cinema_id
                FROM cinema_movies cm
                JOIN cinemas c ON cm.cinema_id = c.cinema_id
                JOIN movies m ON cm.movie_id = m.movie_id
                WHERE cm.movie_id = :mid 
                AND cm.showtime BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
                ORDER BY cm.showtime ASC
            """)
            result = conn.execute(query, {'mid': movie_id})
            rows = result.fetchall()

        # Fetch Weather Data (Manila coordinates)
        weather_url = "https://api.open-meteo.com/v1/forecast?latitude=14.5995&longitude=120.9842&hourly=precipitation_probability&timezone=Asia%2FManila"
        weather_res = requests.get(weather_url)
        weather_data = weather_res.json()['hourly'] if weather_res.status_code == 200 else None

        future_screenings = []
        
        for row in rows:
            # Map RowProxy to dict for easier access if needed
            # (SQLAlchemy rows are accessible by key, so row.showtime works)
            showtime = row.showtime
            
            # 1. Prepare Features for Prediction
            features = pd.DataFrame({
                'day_of_week': [showtime.weekday()],
                'hour': [showtime.hour]
            })
            
            # 2. Predict Crowd Size
            predicted_crowd = model.predict(features)[0]

            # 3. Get Weather Data
            weather_desc = "Clear"
            rain_prob = 0
            
            # Calculate hours difference from now to map to weather API hourly index
            hours_diff = int((showtime - datetime.now()).total_seconds() / 3600)
            
            if weather_data and 0 <= hours_diff < len(weather_data['precipitation_probability']):
                try:
                    rain_prob = weather_data['precipitation_probability'][hours_diff]
                    if rain_prob > 50: weather_desc = "Rainy"
                    elif rain_prob > 20: weather_desc = "Cloudy"
                    else: weather_desc = "Sunny"
                except:
                    pass

            # 4. Calculate Smart Score
            # Formula: Start with 100, subtract for crowds, subtract for rain
            score = 100 - (predicted_crowd * 1.5) - (rain_prob * 0.5)
            
            future_screenings.append({
                'movie_name': row.movie_name,
                'movie_poster': row.movie_poster,
                'cinema_name': row.cinema_name,
                'movie_id': row.movie_id,
                'cinema_id': row.cinema_id,
                'full_date': showtime.strftime('%Y-%m-%d %H:%M:%S'),
                'display_date': showtime.strftime('%a, %b %d'),
                'display_time': showtime.strftime('%I:%M %p'),
                'predicted_crowd': round(predicted_crowd),
                'weather': weather_desc,
                'rain_chance': f"{rain_prob}%",
                'score': round(score, 1)
            })

        # Sort by best score
        future_screenings.sort(key=lambda x: x['score'], reverse=True)

        return jsonify(future_screenings)

    except Exception as e:
        print(f"Error: {e}")
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    app.run(debug=True, port=5000)