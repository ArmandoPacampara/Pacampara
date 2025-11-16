<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MoviEase Booking Steps</title>
  <link rel="stylesheet" href="../../../../public/styles/css/checkout.css">
</head>
<body>
  <div class="booking-container">
    <div class="movie-header">
      <img src="../../../../public/assets/images/blackphone.jpg" alt="Black Phone Movie Poster" class="movie-poster">
      <div class="movie-info">
        <h2 class="movie-title">QUEZON</h2>
        <div class="address" >
          <p id="address">Mandaluyong, Megamall</p>
        </div>
        <div class="cinema_number" >
          <p id="cinema_number">Cinema 1</p>
        </div>
        <div class="schedule" >
          <p id="schedule">7:00 PM</p>
        </div>
        <div class="genre" >
          <p id="genre">Drama</p>
        </div>
        <div class="rate" >
          <p id="rate">PG-13</p>
        </div>
        <div class="movie-desc">
          <h4 style="font-size: 18px;">Movie Description</h4>
          <p>
            A 2025 historical drama that follows the political rise of Philippine President Manuel L. Quezon 
            from his early career to his presidency, depicting his ruthless and charismatic political 
            maneuvering to achieve power.
          </p>
        </div>
      </div>
      <div class="location">
        <select>
          <option>SM - Megamall</option>
          <option>Ayala Malls Manila Bay</option>
          <option>SM North Edsa</option>
        </select>
      </div>
    </div>
   
    <div class="progress-container">
      <div class="progress-bar">
        <div class="progress-line" id="progress-line"></div>
      </div>
      <div class="steps">
        <div class="step" id="step1">
          <div class="circle">✔</div>
          <p>Step 1<br><span>Select Tickets</span></p>
        </div>
        <div class="step" id="step2">
          <div class="circle">✔</div>
          <p>Step 2<br><span>Select Seats</span></p>
        </div>
        <div class="step" id="step3">
          <div class="circle">✔</div>
          <p>Step 3<br><span>Apply Vouchers</span></p>
        </div>
        <div class="step" id="step4">
          <div class="circle">✔</div>
          <p>Step 4<br><span>Confirm Payment</span></p>
        </div>
        <div class="step" id="step5">
          <div class="circle">✔</div>
          <p>Step 5<br><span>Booking Success</span></p>
        </div>
      </div>
    </div>
   
    <div class="step-content" id="step-content">
      <h3>SELECT TICKETS <span>(Available Slots: 40)</span></h3>
      <div class="content-box"></div>
    </div>
    <div class="buttons">
      <button id="prevBtn" disabled>Previous</button>
      <button id="nextBtn">Next</button>
    </div>
  </div>
  <script src="../../../app/controller/Checkout.js"></script>
</body>
</html>