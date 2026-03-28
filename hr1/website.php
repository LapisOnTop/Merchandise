<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Mcores</title>
  <link rel="stylesheet" href="website.css">
  <link rel="icon" type="image/x-icon" href="../assets/TFPH.jpg"/>
</head>
<body>

    <script type="text/javascript"
          src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js">
      </script>
      <script type="text/javascript">
        (function(){
            emailjs.init({
                publicKey: "BCX_ibk88Umt5-E1j",
            });
        })();
     </script>

  <!-- Header -->
  <header class="header">
    <div class="container header-inner">
      <div class="logo">
        <h1><i class="fa-solid fa-chart-line"></i> Mcores </h1>
      </div>

      <div class="menu-toggle" onclick="toggleMenu()">
            <span></span>
            <span></span>
            <span></span>
        </div>

      <nav class="nav">
        <a href="#home">Home</a>
        <a href="#about">About</a>
        <a href="#contact">Contact</a>
        <a href="career.php" target= "blank"  class="btn">Careers</a>
      </nav>
    </div>
  </header>

  <!-- Hero Section -->
  <section id="home" class="hero">
    <video autoplay muted loop playsinline >
      <source src="../upload/banner1.mp4" type="video/mp4">
      Your browser does not support the video tag.
    </video>
    <div class="hero-content" style="position: relative; z-index: 1; text-align: center; color: white;">
      <h2>Merchandising</h2>
      <p>Product display and inventory management for modern retail, employees, and organizational growth.</p>
      <a href="#packages" class="browse-btn">We are hiring!</a>
    </div>
  </section>


  <!-- About Section -->
  <section id="about" class="section">
    <div class="container text-center">
      <h2>About Us</h2>
      <p>We are a leading merchandising solutions provider committed to helping retail businesses succeed through strategic product placement and professional inventory management.</p>
      <p>Our team brings years of expertise in creating outstanding customer experiences. We understand that effective merchandising is crucial to retail success, and we're dedicated to helping businesses maintain clean, organized, and customer-friendly environments.
    </div>
  </section>

  <!-- Contact Section -->
  <section id="contact" class="section section-gray">
    <div class="container contact-grid">
      <div class="contact-form">
        <h2>Contact Us</h2>
        <form action="send_contact.php" method="post" onsubmit="sendMail(event)">
          <input type="text" id="name" name="name" placeholder="Your Name" required />
          <input type="email" id="email" name="email" placeholder="Your Email" required />
          <input type="text" id="subject" placeholder= "Subject" name="subject" required />
          <textarea name="message" id="message" placeholder="Your Message" required></textarea>
          <button type="submit">Send Message</button>
        </form>
      </div>
      <div class="container text-center">
        <h2>Contact informations:</h2>
        <p>Phone: 09298986223</p>
        <p>Email: yuurey01@gmail.com</p>
        <p>Address: Fairview Teraces</p>
      </div>
    </div>
  </section>

  <section id="contact-info">

      <div class="contact-info">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15462.776349024274!2d120.94809060093317!3d14.329193717319585!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397d44c0461d61d%3A0xf371641572939b64!2sDasmari%C3%B1as%2C%204114%20Cavite!5e0!3m2!1sen!2sph!4v1745610237388!5m2!1sen!2sph" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
      </div>
  </section>


  <script>
        function toggleMenu() {
            document.querySelector("nav").classList.toggle("active");
        }

        function sendMail(event){
        event.preventDefault(); // Prevent form from submitting the traditional way

        let parms = {
            name: document.getElementById("name").value,
            email: document.getElementById("email").value,
            subject: document.getElementById("subject").value,
            message: document.getElementById("message").value,
        };

        emailjs.send("service_dpuismq", "template_wty4dts", parms).then(function(response) {
            alert("Email Sent!!");

            // Clear the form fields
            document.getElementById("name").value = "";
            document.getElementById("email").value = "";
            document.getElementById("subject").value = "";
            document.getElementById("message").value = "";
        }, function(error) {
            alert("Failed to send email. Please try again.");
            console.error(error);
        });
    }

    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
  </script>

  <!-- Footer -->
  <footer class="footer">
            <div class="home-content">
                <h1>Mcores</h1>
                <h3 class="typing-text">We are <span></span></h3>
                <p><strong>Phone:</strong> 09298986223</p>
                <p><strong>Email:</strong> yuurey01@gmail.com</p>
                <p><strong>Address:</strong> Fairview Teraces</p>
            </div>     
          <p>&copy; <a href="terms.php">Terms and Conditions</a></p>
    <p>&copy; 20256 Mcores - All Rights Reserved</p>
  </footer>

</body>
</html>
