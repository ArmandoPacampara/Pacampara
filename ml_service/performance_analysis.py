import pandas as pd
import numpy as np
import json
import matplotlib.pyplot as plt
import seaborn as sns
from sqlalchemy import create_engine
from sklearn.ensemble import RandomForestRegressor
from sklearn.model_selection import train_test_split
from sklearn.metrics import r2_score, mean_absolute_error

# --- CONFIGURATION ---
DB_URI = "mysql+mysqlconnector://root:admin@localhost:3307/moviease_db"

# Set matplotlib to non-interactive mode
plt.switch_backend('Agg') 

def analyze_performance():
    print("--- Starting Performance Analysis ---")
    
    # 1. Fetch Data
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
    except Exception as e:
        print(f"Error connecting to DB: {e}")
        return

    # 2. CHECK DATA COUNT (The Fix)
    row_count = len(df)
    print(f"Total Unique Schedules Found: {row_count}")
    
    if row_count < 15:
        print("\n[!] ERROR: Not enough data to calculate accuracy.")
        print(f"    You only have {row_count} schedule entries.")
        print("    Please run the SQL Data Generator script to populate your database with dummy data.")
        return

    # 3. Prepare Features
    df['schedule'] = pd.to_datetime(df['schedule'])
    df['day_of_week'] = df['schedule'].dt.dayofweek
    df['hour'] = df['schedule'].dt.hour
    
    X = df[['day_of_week', 'hour']]
    y = df['attendance']

    # 4. Train Model
    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)
    model = RandomForestRegressor(n_estimators=100, random_state=42)
    model.fit(X_train, y_train)
    
    # 5. Calculate Metrics
    y_pred = model.predict(X_test)
    
    # Handle R2 Score for small datasets
    if len(y_test) < 2:
        r2 = 0.0
        print("Warning: Test set too small for reliable R2 score.")
    else:
        r2 = r2_score(y_test, y_pred)

    mae = mean_absolute_error(y_test, y_pred)
    accuracy_percent = max(0, r2) * 100 

    print(f"R2 Score: {r2:.4f}")
    print(f"Mean Absolute Error: {mae:.2f}")

    # 6. Export Metrics to JSON
    metrics_data = {
        "r2_score": round(r2, 4) if not np.isnan(r2) else 0.0,
        "accuracy_percent": round(accuracy_percent, 1) if not np.isnan(accuracy_percent) else 0.0,
        "mae": round(mae, 2),
        "total_samples": row_count,
        "last_updated": pd.Timestamp.now().strftime("%Y-%m-%d %H:%M:%S")
    }
    
    with open('analytics_metrics.json', 'w') as f:
        json.dump(metrics_data, f)
    
    # 7. Generate Charts (Fixed Warning)
    
    # Chart A: Feature Importance
    plt.figure(figsize=(8, 5))
    importances = model.feature_importances_
    features = ['Day of Week', 'Hour of Day']
    
    # FIX: Added hue=features and legend=False to satisfy Seaborn update
    sns.barplot(x=features, y=importances, hue=features, palette="viridis", legend=False)
    
    plt.title("What Drives Attendance?")
    plt.ylabel("Importance Score")
    plt.savefig('chart_features.png')
    plt.close()
    
    # Chart B: Actual vs Predicted
    plt.figure(figsize=(8, 5))
    plt.scatter(y_test, y_pred, alpha=0.6, color='#ff4b4b')
    
    # Robust min/max checking
    if len(y_test) > 0:
        min_val = min(y.min(), y_test.min())
        max_val = max(y.max(), y_test.max())
        plt.plot([min_val, max_val], [min_val, max_val], 'k--', lw=2)
        
    plt.xlabel("Actual Attendance")
    plt.ylabel("Predicted Attendance")
    plt.title("Prediction Accuracy")
    plt.savefig('chart_accuracy.png')
    plt.close()

    print("--- Analysis Complete. Files Generated. ---")

if __name__ == "__main__":
    analyze_performance()