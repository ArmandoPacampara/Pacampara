import pandas as pd
import random
from datetime import datetime, timedelta
from sqlalchemy import create_engine, text

# --- CONFIGURATION ---
DB_URI = "mysql+mysqlconnector://root:admin@localhost:3307/moviease_db"

def generate_data():
    engine = create_engine(DB_URI)
    bookings = []
    
    print("--- Generating Dummy Data ---")

    # 1. Fetch valid Movie IDs (To avoid Foreign Key errors)
    try:
        print("Fetching existing movies...")
        with engine.connect() as conn:
            movies_df = pd.read_sql("SELECT movie_id FROM movies", conn)
            movie_ids = movies_df['movie_id'].tolist()
            
        if not movie_ids:
            print("❌ Error: No movies found in 'movies' table. Please add a movie first.")
            return
            
    except Exception as e:
        print(f"❌ Database Error: {e}")
        return
    
    # 2. Create showtimes (Last 30 days)
    base_date = datetime.now() - timedelta(days=30)
    schedules = []
    
    for day in range(30):
        current_day = base_date + timedelta(days=day)
        # Create 3 slots per day
        schedules.append(current_day.replace(hour=10, minute=0, second=0)) # 10 AM
        schedules.append(current_day.replace(hour=14, minute=0, second=0)) # 2 PM
        schedules.append(current_day.replace(hour=19, minute=0, second=0)) # 7 PM

    # 3. Generate Bookings
    print(f"Generating bookings for {len(schedules)} time slots...")
    
    for schedule in schedules:
        # Smart Logic: Weekends & Evenings are busier
        is_weekend = schedule.weekday() >= 4 # Fri, Sat, Sun
        is_evening = schedule.hour >= 18
        
        # Base attendance
        attendance = random.randint(3, 8)
        
        # Boost attendance for prime times
        if is_weekend: attendance += random.randint(15, 30)
        if is_evening: attendance += random.randint(10, 20)
        
        for _ in range(attendance):
            bookings.append({
                'user_id': random.randint(1, 5), # Ensure these User IDs exist or use 1
                'movie_id': random.choice(movie_ids), # <--- FIXED: Uses real Movie IDs
                'schedule': schedule,
                'date_booked': datetime.now().date(), # <--- FIXED: Matches your schema
                'price': 300.00,                      # <--- FIXED: 'price' not 'total_price'
                'final_price': 300.00,                # <--- Added for completeness
                'status': 'Completed'
            })

    # 4. Insert into Database
    if bookings:
        df = pd.DataFrame(bookings)
        try:
            df.to_sql('booking', con=engine, if_exists='append', index=False)
            print(f"✅ Success! Inserted {len(df)} booking records.")
        except Exception as e:
            print(f"❌ Error inserting data: {e}")
    else:
        print("No bookings generated.")

if __name__ == "__main__":
    generate_data()