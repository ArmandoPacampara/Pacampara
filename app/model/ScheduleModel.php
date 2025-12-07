<?php
// app/model/ScheduleModel.php
require_once __DIR__ . '/../core/db.php';

class ScheduleModel {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Fetch schedules grouped by Cinema -> Date -> Time (Used in Booking)
    public function getSchedulesByMovie($movie_id) {
        $query = "
            SELECT 
                c.cinema_id,
                c.cinema_name,
                c.cinema_address,
                DATE(cm.showtime) AS show_date, 
                TIME_FORMAT(cm.showtime, '%h:%i%p') AS show_time_ampm,
                cm.showtime AS full_showtime
            FROM cinema_movies cm
            JOIN cinemas c ON cm.cinema_id = c.cinema_id
            WHERE cm.movie_id = ? 
            ORDER BY c.cinema_name, show_date, TIME(cm.showtime)
        ";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $movie_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $schedules_grouped = [];
        while ($schedule = $result->fetch_assoc()) {
            $cinema_name = $schedule['cinema_name'];
            $show_date = $schedule['show_date'];
            
            if (!isset($schedules_grouped[$cinema_name])) {
                $schedules_grouped[$cinema_name] = [
                    'id' => $schedule['cinema_id'],
                    'address' => $schedule['cinema_address'],
                    'dates' => []
                ];
            }
            
            $schedules_grouped[$cinema_name]['dates'][$show_date][] = [
                'time' => $schedule['show_time_ampm'],
                'full_showtime' => $schedule['full_showtime']
            ];
        }
        
        return $schedules_grouped;
    }

    // --- NEW: FETCH ALL UPCOMING SCHEDULES (Grouped by Date) ---
    public function getUpcomingSchedules() {
        $query = "
            SELECT 
                m.movie_id, m.movie_name, m.movie_poster, m.genre, m.movie_class,
                c.cinema_id, c.cinema_name, 
                cm.showtime, 
                cm.ticket_price
            FROM cinema_movies cm
            JOIN movies m ON cm.movie_id = m.movie_id
            JOIN cinemas c ON cm.cinema_id = c.cinema_id
            WHERE cm.showtime >= NOW()
            ORDER BY cm.showtime ASC
        ";
        
        $result = $this->conn->query($query);
        
        $grouped = [];
        while($row = $result->fetch_assoc()) {
            $date = date('Y-m-d', strtotime($row['showtime']));
            $grouped[$date][] = $row;
        }
        
        return $grouped;
    }
}
?>