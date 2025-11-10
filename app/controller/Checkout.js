const steps = document.querySelectorAll(".step");
const nextBtn = document.getElementById("nextBtn");
const prevBtn = document.getElementById("prevBtn");
const progressLine = document.getElementById("progress-line");
const contentBox = document.getElementById("step-content");

let currentStep = 0;

const stepTitles = [
  "SELECT TICKETS <span>(Available Slots: 40)</span>",
  "SELECT SEATS",
  "APPLY VOUCHERS",
  "CONFIRM PAYMENT",
  "BOOKING SUCCESS"
];

function updateSteps() {
  steps.forEach((step, index) => {
    step.classList.remove("active", "completed");
    if (index < currentStep) step.classList.add("completed");
    if (index === currentStep) step.classList.add("active");
  });

  // Update red line width
  const progressWidth = (currentStep / (steps.length - 1)) * 100;
  progressLine.style.width = `${progressWidth}%`;

  // Update content
  contentBox.innerHTML = `
    <h3>${stepTitles[currentStep]}</h3>
    <div class="content-box"></div>
  `;

  // Button states
  prevBtn.disabled = currentStep === 0;
  nextBtn.textContent = currentStep === steps.length - 1 ? "Finish" : "Next";
}

nextBtn.addEventListener("click", () => {
  if (currentStep < steps.length - 1) {
    currentStep++;
    updateSteps();
  }
});

prevBtn.addEventListener("click", () => {
  if (currentStep > 0) {
    currentStep--;
    updateSteps();
  }
});

updateSteps();
