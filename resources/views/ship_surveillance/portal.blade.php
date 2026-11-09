<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
  
  <title>BKK Bandung - Formulir Karantina Kesehatan (TANGKAP)</title>
  <meta name="description" content="Formulir karantina kesehatan untuk kedatangan kapal di Balai Kekarantinaan Kesehatan Kelas I Bandung">
  <meta name="author" content="IT BKK Bandung">
  
  <!-- Preload critical resources -->
  <link rel="preconnect" href="https://cdn.jsdelivr.net">
  <link rel="preconnect" href="https://unpkg.com">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js" defer></script>

  <style>
    :root {
      --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      --success-gradient: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
      --info-gradient: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
      --text-primary: #2c3e50;
      --text-secondary: #7f8c8d;
      --text-light: rgba(255, 255, 255, 0.9);
      --shadow-light: 0 10px 25px rgba(0, 0, 0, 0.15);
      --shadow-heavy: 0 20px 40px rgba(0, 0, 0, 0.1);
      --border-radius: 20px;
      --border-radius-sm: 15px;
      --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    * {
      box-sizing: border-box;
    }

    body {
      background: var(--primary-gradient);
      min-height: 100vh;
      font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
      line-height: 1.6;
      margin: 0;
      padding: 0;
      overflow-x: hidden;
    }

    .container-fluid {
      padding: 1rem;
    }

    .logo-container {
      text-align: center;
      margin-bottom: clamp(1rem, 4vw, 2rem);
    }

    .logo-container img {
      max-width: 100%;
      height: auto;
      max-height: clamp(55px, 10vw, 85px); 
      filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.12));
    }

    .main-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(15px);
      border-radius: var(--border-radius);
      box-shadow: var(--shadow-heavy);
      padding: clamp(1.5rem, 5vw, 3rem);
      margin: clamp(1rem, 3vw, 2rem) auto;
      max-width: min(90vw, 900px);
      width: 100%;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .main-title {
      color: var(--text-primary);
      font-weight: 700;
      font-size: clamp(1.4rem, 3.5vw, 2.3rem);
      text-align: center;
      margin-bottom: clamp(2rem, 5vw, 3rem);
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.08);
      line-height: 1.3;
      letter-spacing: -0.02em;
    }

    .subtitle {
      font-size: clamp(0.95rem, 2.2vw, 1.35rem);
      font-weight: 500;
      color: var(--text-secondary);
      display: block;
      margin-top: 0.5rem;
      letter-spacing: -0.01em;
    }

    .options-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: clamp(1rem, 3vw, 1.5rem);
      margin-bottom: 2rem;
    }

    .option-card {
      background: var(--primary-gradient);
      color: white;
      border-radius: var(--border-radius-sm);
      padding: clamp(1.5rem, 4vw, 2rem);
      text-decoration: none;
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      transition: var(--transition);
      box-shadow: var(--shadow-light);
      border: 1px solid rgba(255, 255, 255, 0.1);
      position: relative;
      overflow: hidden;
    }

    .option-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(45deg, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
      opacity: 0;
      transition: var(--transition);
    }

    .option-card:hover {
      transform: translateY(-8px) scale(1.02);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
      color: white;
      text-decoration: none;
    }

    .option-card:hover::before {
      opacity: 1;
    }

    .option-card:focus {
      outline: 3px solid rgba(255, 255, 255, 0.5);
      outline-offset: 2px;
    }

    .option-card .icon {
      font-size: clamp(2.5rem, 6vw, 3.5rem);
      margin-bottom: 1rem;
      display: block;
      filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
    }

    .option-card h3 {
      font-size: clamp(1.2rem, 3vw, 1.8rem);
      font-weight: 600;
      margin-bottom: 1rem;
      line-height: 1.3;
      letter-spacing: -0.01em;
    }

    .option-card p {
      font-size: clamp(0.9rem, 2vw, 1.1rem);
      opacity: 0.95;
      margin-bottom: 0;
      line-height: 1.5;
      max-width: 280px;
    }

    .domestic-card {
      background: var(--success-gradient);
    }

    .international-card {
      background: var(--info-gradient);
    }

    .footer-section {
      margin-top: clamp(2rem, 5vw, 3rem);
      text-align: center;
    }

    .footer-text {
      color: var(--text-light);
      font-size: clamp(0.85rem, 2vw, 0.95rem);
      line-height: 1.6;
      margin-bottom: 1.5rem;
    }

    .copyright {
      padding-top: 1rem;
      border-top: 1px solid rgba(255, 255, 255, 0.2);
    }

    .copyright p {
      color: rgba(255, 255, 255, 0.7);
      font-size: clamp(0.75rem, 1.5vw, 0.85rem);
      margin: 0;
      font-weight: 300;
    }

    .admin-link {
      display: inline-block;
      margin-top: 10px;
      color: rgba(255, 255, 255, 0.85);
      font-size: 0.8rem;
      text-decoration: underline;
      transition: color 0.2s;
    }
    .admin-link:hover {
      color: #ffffff;
    }

    @media (max-width: 576px) {
      .container-fluid {
        padding: 0.5rem;
      }
      .options-container {
        grid-template-columns: 1fr;
        gap: 1rem;
      }
      .option-card {
        min-height: 180px;
      }
    }
  </style>
</head>

<body>
  <div class="container-fluid">
    <div class="row justify-content-center align-items-center min-vh-100">
      <div class="col-12">
        
        <!-- Logo -->
        <div class="logo-container" data-aos="fade-down" data-aos-duration="800">
          <img 
            src="{{ asset('storage/images/LOGO KEMENKES & KARANTINA.png') }}" 
            alt="BKK Bandung Logo" 
            class="img-fluid" 
            loading="lazy"
            onerror="this.onerror=null;this.src='{{ asset('images/LOGO KEMENKES & KARANTINA.png') }}';"
          >
        </div>

        <!-- Main Card -->
        <main class="main-card" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200" role="main">
          
          <!-- Title -->
          <header>
            <h1 class="main-title" data-aos="fade-up" data-aos-duration="800" data-aos-delay="400">
              Digitalisasi Pengawasan Kekarantinaan Kesehatan di Pelabuhan (TANGKAP)
              <br>
              <span class="subtitle">QUESTIONS FOR SHIP ARRIVALS</span>
            </h1>
          </header>

          <!-- Options -->
          <section class="options-container" aria-label="Pilihan formulir karantina">
            <a href="{{ route('ship-surveillance.assessment.domestic') }}" 
               class="option-card domestic-card" 
               data-aos="fade-right" 
               data-aos-duration="800" 
               data-aos-delay="600"
               aria-label="Formulir untuk kapal dari dalam negeri">
              <i class="icon fas fa-flag" aria-hidden="true"></i>
              <h3>DARI DALAM NEGERI</h3>
              <p>Formulir untuk kapal yang datang dari pelabuhan dalam negeri Indonesia</p>
            </a>
            
            <a href="{{ route('ship-surveillance.assessment.international') }}" 
               class="option-card international-card" 
               data-aos="fade-left" 
               data-aos-duration="800" 
               data-aos-delay="800"
               aria-label="Form for ships from abroad">
              <i class="icon fas fa-globe-americas" aria-hidden="true"></i>
              <h3>SHIP ARRIVING FROM ABROAD</h3>
              <p>Form for ships arriving from foreign ports</p>
            </a>
          </section>

        </main>

        <!-- Footer -->
        <footer class="footer-section" data-aos="fade-up" data-aos-duration="800" data-aos-delay="1000">
          <div class="footer-text">
            <p>
              <strong>Balai Kekarantinaan Kesehatan Kelas I Bandung</strong><br>
              Kementerian Kesehatan Republik Indonesia
            </p>
            <a href="{{ url('/ship-surveillance') }}" class="admin-link">
              <i class="fas fa-arrow-left"></i> Masuk ke Dashboard Petugas Pengawasan Kapal
            </a>
          </div>

          <!-- Copyright -->
          <div class="copyright" data-aos="fade-up" data-aos-duration="600" data-aos-delay="1200">
            <p>&copy; {{ date('Y') }} IT BKK Bandung. All Rights Reserved.</p>
          </div>
        </footer>

      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      if (typeof AOS !== 'undefined') {
        AOS.init({
          duration: 800,
          easing: 'ease-in-out',
          once: true,
          offset: 50
        });
      }
    });
  </script>

</body>

</html>
