'use strict';



/**
 * add Event on elements
 */

const addEventOnElem = function (elem, type, callback) {
  if (elem.length > 1) {
    for (let i = 0; i < elem.length; i++) {
      elem[i].addEventListener(type, callback);
    }
  } else {
    elem.addEventListener(type, callback);
  }
}



/**
 * navbar toggle
 */

const navbar = document.querySelector("[data-navbar]");
const navTogglers = document.querySelectorAll("[data-nav-toggler]");
const navbarLinks = document.querySelectorAll("[data-nav-link]");
const overlay = document.querySelector("[data-overlay]");

const toggleNavbar = function () {
  navbar.classList.toggle("active");
  overlay.classList.toggle("active");
}

addEventOnElem(navTogglers, "click", toggleNavbar);

const closeNavbar = function () {
  navbar.classList.remove("active");
  overlay.classList.remove("active");
}

addEventOnElem(navbarLinks, "click", closeNavbar);



/**
 * header & back top btn show when scroll down to 100px
 */

const header = document.querySelector("[data-header]");
const backTopBtn = document.querySelector("[data-back-top-btn]");

const headerActive = function () {
  if (window.scrollY > 80) {
    header.classList.add("active");
    backTopBtn.classList.add("active");
  } else {
    header.classList.remove("active");
    backTopBtn.classList.remove("active");
  }
}

addEventOnElem(window, "scroll", headerActive);

/**
 * filter tabs on the project page
 */

document.addEventListener("DOMContentLoaded", () => {
  const filterButtons = document.querySelectorAll(".filter-btn");
  const gridItems = document.querySelectorAll(".grid-list li");

  filterButtons.forEach((button) => {
    button.addEventListener("click", () => {
      // Remove active class from all buttons
      filterButtons.forEach((btn) => btn.classList.remove("active"));
      // Add active class to the clicked button
      button.classList.add("active");

      const filterValue = button.getAttribute("data-filter");

      // Show/Hide grid items based on filter
      gridItems.forEach((item) => {
        const categoryAttr = item.getAttribute("data-category");
        if (categoryAttr) { // Check if categoryAttr is not null
          const categories = categoryAttr.split(" ");
          if (filterValue === "all" || categories.includes(filterValue)) {
            item.style.display = "block";
          } else {
            item.style.display = "none";
          }
        } else {
          // Optionally handle items without a data-category attribute
          item.style.display = "none"; // or any other logic you want
        }
      });
    });
  });

  /**
   * Update copyright year
   */
  const yearElements = document.querySelectorAll('.current-year');
  const currentYear = new Date().getFullYear();
  
  yearElements.forEach(element => {
    element.textContent = currentYear;
  });

  /**
   * Contact form handling
   */
  const contactForm = document.getElementById("contact-form");
  const successMessage = document.getElementById("success-message");
  const errorMessage = document.getElementById("error-message");

  if (contactForm) {
    contactForm.addEventListener("submit", function(e) {
      e.preventDefault();
      // Get form data
      const formData = new FormData(this);
      // Client-side validation
      const name = formData.get('name').trim();
      const email = formData.get('email').trim();
      const message = formData.get('message').trim();
      // Google reCAPTCHA validation
      const recaptcha = formData.get('g-recaptcha-response');
      if (!name || !email || !message || !recaptcha) {
        if (errorMessage) {
          errorMessage.textContent = "Please fill in all required fields and complete the security verification.";
          errorMessage.classList.add("show");
        }
        return;
      }
      // Basic email validation
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) {
        if (errorMessage) {
          errorMessage.textContent = "Please enter a valid email address.";
          errorMessage.classList.add("show");
        }
        return;
      }
      // Show loading state
      const submitBtn = this.querySelector('button[type="submit"]');
      const originalText = submitBtn.textContent;
      submitBtn.textContent = "Sending...";
      submitBtn.disabled = true;
      // Clear previous messages
      if (successMessage) {
        successMessage.textContent = "";
        successMessage.classList.remove("show");
      }
      if (errorMessage) {
        errorMessage.textContent = "";
        errorMessage.classList.remove("show");
      }
      // Send form data via AJAX
      fetch("../send_email.php", {
        method: "POST",
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Show success message
          if (successMessage) {
            successMessage.textContent = data.message;
            successMessage.classList.add("show");
          }
          // Reset form
          contactForm.reset();
          // Scroll to success message
          if (successMessage) {
            successMessage.scrollIntoView({ behavior: "smooth", block: "center" });
          }
        } else {
          // Show error message
          if (errorMessage) {
            errorMessage.textContent = data.message;
            errorMessage.classList.add("show");
          }
        }
      })
      .catch(error => {
        console.error("Error:", error);
        if (errorMessage) {
          errorMessage.textContent = "An error occurred. Please try again or contact us directly.";
          errorMessage.classList.add("show");
        }
      })
      .finally(() => {
        // Reset button state
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
      });
    });
  }
});
