<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AI Performance Analytics</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f6f9; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        .card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .metric-box { text-align: center; padding: 15px; border: 1px solid #eee; border-radius: 8px; }
        .metric-val { font-size: 24px; font-weight: bold; color: #2c3e50; }
        .metric-label { font-size: 14px; color: #7f8c8d; }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; }
        h1 { color: #2c3e50; }
        .status { font-size: 0.9em; color: green; margin-bottom: 20px; display: block;}
        img { max-width: 100%; border-radius: 5px; }
        .btn { background: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
        .btn:hover { background: #2980b9; }
    </style>
</head>
<body>

<div class="container">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h1>📊 Scheduler AI Performance</h1>
        <form method="post">
            <button type="submit" name="run_analysis" class="btn">Refresh Analysis</button>
        </form>
    </div>

    <?php
    // OPTIONAL: Run Python script from PHP
    if(isset($_POST['run_analysis'])) {
        // Adjust 'python' to your python path if needed (e.g. 'C:\\Python39\\python.exe')
        $output = shell_exec('python performance_analysis.py 2>&1');
        echo "<pre style='background:#333; color:#0f0; padding:10px;'>$output</pre>";
    }

    // Read the JSON data
    $json_file = 'analytics_metrics.json';
    if (file_exists($json_file)) {
        $data = json_decode(file_get_contents($json_file), true);
    } else {
        $data = null;
    }
    ?>

    <?php if ($data): ?>
        <span class="status">Last Updated: <?php echo $data['last_updated']; ?></span>

        <div class="card">
            <h3>Key Metrics</h3>
            <div class="grid">
                <div class="metric-box">
                    <div class="metric-val"><?php echo $data['accuracy_percent']; ?>%</div>
                    <div class="metric-label">Model Reliability (R²)</div>
                </div>
                <div class="metric-box">
                    <div class="metric-val"><?php echo $data['mae']; ?></div>
                    <div class="metric-label">Avg. Error (Tickets)</div>
                </div>
                <div class="metric-box">
                    <div class="metric-val"><?php echo $data['total_samples']; ?></div>
                    <div class="metric-label">Training Data Points</div>
                </div>
            </div>
            <p style="margin-top:15px; font-size:0.9em; color:#666;">
                <i>Interpretation:</i> An error of <b><?php echo $data['mae']; ?></b> means if the AI predicts 100 people, the actual crowd is usually between <?php echo 100 - $data['mae']; ?> and <?php echo 100 + $data['mae']; ?>.
            </p>
        </div>

        <div class="grid" style="grid-template-columns: 1fr 1fr;">
            <div class="card">
                <h3>Prediction Accuracy</h3>
                <img src="chart_accuracy.png?t=<?php echo time(); ?>" alt="Accuracy Chart">
            </div>
            <div class="card">
                <h3>Feature Importance</h3>
                <img src="chart_features.png?t=<?php echo time(); ?>" alt="Feature Chart">
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <h3 style="color:red;">No Analysis Data Found</h3>
            <p>Please click "Refresh Analysis" or run <code>python performance_analysis.py</code> in your terminal.</p>
        </div>
    <?php endif; ?>

</div>

</body>
</html>