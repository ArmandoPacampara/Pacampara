import pandas as pd
from sqlalchemy import create_engine, text
# Import numpy for general calculations (though not strictly needed just for R2)
import numpy as np 
# 💥 CHANGED: Import r2_score instead of mean_squared_error
from sklearn.metrics import r2_score 
from sklearn.ensemble import RandomForestRegressor
from sklearn.model_selection import train_test_split 
from flask import Flask, jsonify, request
import requests
from datetime import datetime, timedelta

app = Flask(__name__)

# --- CONFIGURATION ---
DB_URI = "mysql+mysqlconnector://root:admin@localhost:3307/moviease_db"

# --- 1. FETCH HISTORICAL DATA ---
def get_training_data():
    try:
        engine = create_engine(DB_URI)
        with engine.connect() as conn:
            query = """
            SELECT schedule, COUNT(ticket_id) as attendance 
            FROM booking 
            WHERE status = 'Completed' 
            GROUP BY schedule
            """
            df = pd.read_sql(query, conn)
        
        if df.empty:
            # Fallback data
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
        df['day_of_week'] = df['schedule'].dt.dayofweek
        df['hour'] = df['schedule'].dt.hour
        
    return df

# --- 2. TRAIN MODEL ---
print("Training Model...")
df = get_training_data()
# Added random_state for reproducible results
model = RandomForestRegressor(n_estimators=100, random_state=42) 

if not df.empty:
    features = ['day_of_week', 'hour']
    X = df[features]
    y = df['attendance']
    
    # Perform Train/Test Split (80% train, 20% test)
    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)
    
    # Train the model using only the training data
    model.fit(X_train, y_train)
    
    # --- PERFORMANCE METRICS & FEATURE IMPORTANCE ---
    
    # 1. Predict on the test set
    y_pred = model.predict(X_test)
    
    # 2. Calculate R-squared (R²) Score
    # R2 ranges from -∞ to 1.0. Higher is better.
    r_squared = r2_score(y_test, y_pred)
    
    # 3. Extract Feature Importance
    importances = model.feature_importances_
    
    print("-" * 50)
    print(f"Model Trained Successfully on {len(X_train)} samples.")
    # 💥 UPDATED: Print R-squared score
    print(f"Performance Metric (R-squared Score on Test Set): {r_squared:.4f}")
    print("\nFeature Importance:")
    for feature, importance in zip(features, importances):
        # Day 0 = Monday, Day 6 = Sunday
        print(f"  {feature}: {importance:.4f}")
    print("-" * 50)
else:
    print("Warning: No training data available.")

# --- 3. GET WEATHER FORECAST ---
def get_weather_forecast():
    url = "https://api.open-meteo.com/v1/forecast?latitude=14.5995&longitude=120.9842&hourly=weathercode,temperature_2m,precipitation_probability&timezone=Asia%2FManila"
    try:
        response = requests.get(url).json()
        return response['hourly']
    except:
        return None

# --- 4. PREDICTION ENDPOINT (UPDATED FOR FILTERING) ---
@app.route('/predict_best_time', methods=['GET'])
def predict():
    # Get movie_id from the URL parameter (e.g., ?movie_id=1)
    movie_id = request.args.get('movie_id')
    
    engine = create_engine(DB_URI)
    future_screenings = []
    
    try:
        with engine.connect() as conn:
            # Base Query
            sql = """
            SELECT 
                cm.showtime, 
                cm.cinema_id,
                cm.movie_id,
                m.movie_name, 
                m.movie_poster, 
                c.cinema_name
            FROM cinema_movies cm
            JOIN movies m ON cm.movie_id = m.movie_id
            JOIN cinemas c ON cm.cinema_id = c.cinema_id
            WHERE cm.showtime >= NOW() 
            AND cm.showtime <= DATE_ADD(NOW(), INTERVAL 7 DAY)
            """
            
            # Add specific movie filter if provided
            params = {}
            if movie_id:
                sql += " AND cm.movie_id = :mid"
                params['mid'] = movie_id
            
            sql += " ORDER BY cm.showtime ASC"
            
            # Use SQLAlchemy text() for safe parameter binding
            schedules_df = pd.read_sql(text(sql), conn, params=params)
            
    except Exception as e:
        return jsonify({"error": str(e)})

    if schedules_df.empty:
        return jsonify([])

    weather_data = get_weather_forecast()
    
    # Score Each Screening
    for index, row in schedules_df.iterrows():
        showtime = pd.to_datetime(row['showtime'])
        
        # Predict Crowd
        day = showtime.dayofweek
        hour = showtime.hour
        
        input_data = pd.DataFrame([[day, hour]], columns=['day_of_week', 'hour'])
        try:
            predicted_crowd = model.predict(input_data)[0]
        except:
            predicted_crowd = 50 

        # Get Weather
        weather_desc = "Clear"
        rain_prob = 0
        
        hours_diff = int((showtime - datetime.now()).total_seconds() / 3600)
        
        if weather_data and 0 <= hours_diff < len(weather_data['precipitation_probability']):
            try:
                rain_prob = weather_data['precipitation_probability'][hours_diff]
                if rain_prob > 50: weather_desc = "Rainy"
                elif rain_prob > 20: weather_desc = "Cloudy"
                else: weather_desc = "Sunny"
            except:
                pass

        # Calculate Smart Score
        score = 100 - (predicted_crowd * 1.5) - (rain_prob * 0.5)
        
        future_screenings.append({
            'movie_name': row['movie_name'],
            'movie_poster': row['movie_poster'],
            'cinema_name': row['cinema_name'],
            'movie_id': row['movie_id'],
            'cinema_id': row['cinema_id'],
            'full_date': showtime.strftime('%Y-%m-%d %H:%M:%S'),
            'display_date': showtime.strftime('%a, %b %d'),
            'display_time': showtime.strftime('%I:%M %p'),
            'predicted_crowd': round(predicted_crowd),
            'weather': weather_desc,
            'rain_chance': f"{rain_prob}%",
            'score': round(score, 1)
        })

    # Return Top 5 best times for THIS movie
    best_screenings = sorted(future_screenings, key=lambda x: x['score'], reverse=True)[:5]
    
    return jsonify(best_screenings)

if __name__ == '__main__':
    print("Starting ML API with Filtering...")
    app.run(port=5000, debug=True)