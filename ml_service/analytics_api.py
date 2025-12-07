import matplotlib
matplotlib.use('Agg') 
import matplotlib.pyplot as plt
import seaborn as sns
import pandas as pd
from sqlalchemy import create_engine, text
from flask import Flask, jsonify, request
import io
import base64

app = Flask(__name__)

# --- CONFIGURATION ---
DB_URI = "mysql+mysqlconnector://root:admin@localhost:3307/moviease_db"

def get_db_connection():
    return create_engine(DB_URI).connect()

def fig_to_base64(fig):
    img = io.BytesIO()
    fig.savefig(img, format='png', bbox_inches='tight')
    img.seek(0)
    return base64.b64encode(img.getvalue()).decode()

@app.route('/get_reports', methods=['GET'])
def get_reports():
    plots = {}
    cinema_id = request.args.get('cinema_id') # Get filter if exists
    
    try:
        conn = get_db_connection()
        
        # Base WHERE clause parts
        filter_sql = ""
        params = {}
        
        if cinema_id:
            # We need to join tables to filter by cinema
            join_sql = "JOIN booked_seats bs ON b.ticket_id = bs.ticket_id JOIN seats s ON bs.seat_id = s.seat_id"
            filter_sql = "AND s.cinema_id = :cid"
            params['cid'] = cinema_id
        else:
            join_sql = ""

        # ----------------------------------------
        # 1. SALES TREND (Line Chart)
        # ----------------------------------------
        query_sales = text(f"""
            SELECT b.date_booked, SUM(b.final_price) as total_sales 
            FROM booking b
            {join_sql}
            WHERE b.status = 'Completed' {filter_sql}
            GROUP BY b.date_booked 
            ORDER BY b.date_booked ASC
        """)
        df_sales = pd.read_sql(query_sales, conn, params=params)
        
        if not df_sales.empty:
            df_sales['date_booked'] = pd.to_datetime(df_sales['date_booked'])
            plt.figure(figsize=(10, 5))
            sns.set_style("whitegrid")
            sns.lineplot(data=df_sales, x='date_booked', y='total_sales', marker='o', color='#d60000', linewidth=2.5)
            plt.title('Revenue Trend', fontsize=14, fontweight='bold')
            plt.xlabel('Date')
            plt.ylabel('Revenue (PHP)')
            plt.xticks(rotation=45)
            plt.tight_layout()
            plots['sales_trend'] = fig_to_base64(plt.gcf())
            plt.close()
        else:
            plots['sales_trend'] = None

        # ----------------------------------------
        # 2. TOP 5 MOVIES (Bar Chart)
        # ----------------------------------------
        query_movies = text(f"""
            SELECT m.movie_name, SUM(b.final_price) as revenue
            FROM booking b
            JOIN movies m ON b.movie_id = m.movie_id
            {join_sql}
            WHERE b.status = 'Completed' {filter_sql}
            GROUP BY m.movie_name
            ORDER BY revenue DESC
            LIMIT 5
        """)
        df_movies = pd.read_sql(query_movies, conn, params=params)
        
        if not df_movies.empty:
            plt.figure(figsize=(10, 5))
            sns.barplot(data=df_movies, x='revenue', y='movie_name', palette='Reds_r')
            plt.title('Top Movies by Revenue', fontsize=14, fontweight='bold')
            plt.xlabel('Total Revenue (PHP)')
            plt.ylabel('')
            plt.tight_layout()
            plots['top_movies'] = fig_to_base64(plt.gcf())
            plt.close()
        else:
            plots['top_movies'] = None

        # ----------------------------------------
        # 3. GENRE DISTRIBUTION (Pie Chart)
        # ----------------------------------------
        query_genre = text(f"""
            SELECT m.genre, COUNT(b.ticket_id) as bookings
            FROM booking b
            JOIN movies m ON b.movie_id = m.movie_id
            {join_sql}
            WHERE b.status = 'Completed' {filter_sql}
            GROUP BY m.genre
        """)
        df_genre = pd.read_sql(query_genre, conn, params=params)
        
        if not df_genre.empty:
            plt.figure(figsize=(6, 6))
            colors = sns.color_palette('pastel')[0:len(df_genre)]
            plt.pie(df_genre['bookings'], labels=df_genre['genre'], colors=colors, autopct='%.1f%%')
            plt.title('Bookings by Genre', fontsize=14, fontweight='bold')
            plt.tight_layout()
            plots['genre_dist'] = fig_to_base64(plt.gcf())
            plt.close()
        else:
            plots['genre_dist'] = None

        conn.close()
        return jsonify(plots)

    except Exception as e:
        return jsonify({"error": str(e)})

if __name__ == '__main__':
    # Restart this script after saving!
    print("Starting Analytics API on port 5001...")
    app.run(port=5001, debug=True)