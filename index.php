<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ReserveSpot</title>
  <style>
    html {
      scroll-behavior: smooth;
    }
  </style>
  <style>
 
  ::-webkit-scrollbar {
    width: 6px;
  }

  ::-webkit-scrollbar-track {
    background: transparent;
  }

  ::-webkit-scrollbar-thumb {
    background-color: #aaa;
    border-radius: 10px;
    border: 1px solid transparent;
  }

  ::-webkit-scrollbar-thumb:hover {
    background-color: #888;
  }

</style>

  <canvas id="canvas-bg" class="fixed top-0 left-0 w-full h-full -z-10"></canvas>

  <script src="https://cdn.tailwindcss.com"></script>

  <script src="assets/js/canvas-bg.js" defer></script>

  <style>
    .glass {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
  </style>
</head>
<body class="relative bg-gradient-to-b from-[#fffaf0] to-[#e6fff7] min-h-screen text-black font-sans overflow-x-hidden scroll-smooth">


  <canvas id="canvas-bg" class="fixed top-0 left-0 w-full h-full -z-10"></canvas>

  <?php require_once 'includes/header-guest.php'; ?>

  <section class="relative z-10 min-h-screen w-full flex flex-col lg:flex-row items-center justify-center lg:justify-end px-4 md:px-10 py-10 bg-gradient-to-b from-[#fffaf0] to-[#e6fff7]">

    <div class="relative max-w-xl mb-10 lg:mb-0 lg:absolute lg:top-36 lg:left-16 px-4 text-center lg:text-left">
      <h2 class="text-4xl font-bold text-[#131e5a] font-[Plus Jakarta Sans] leading-snug">
        “Reading is essential for those who seek to rise above the ordinary!”
      </h2>
      <div class="mt-8 flex justify-center lg:justify-start z-10 relative">
  <a href="./register.php"
     class="px-8 py-3 bg-[#131e5a] text-white rounded-xl text-lg font-semibold transition transform border border-white shadow-[2px_2px_0px_0px_rgba(0,0,0,0.5)] hover:shadow-[1px_1px_0px_0px_rgba(0,0,0,0.5)] hover:translate-x-[2px] hover:translate-y-[2px]">
    Sign Up - Your Library
  </a>
</div>

    </div>

    <div class="flex items-center justify-center lg:justify-end">
      <div class="relative z-10">
        <div class="relative before:content-[''] before:absolute before:inset-0 before:rounded-full before:border-[6px] before:border-teal-400 before:animate-pulse before:z-0">
          <img src="assets/img/st.jpg" alt="Student Reading"
               class="relative z-10 rounded-full w-[320px] h-[320px] object-cover border-4 border-white shadow-xl">
        </div>
      </div>

      <div class="flex flex-col justify-center items-center ml-[-40px] space-y-[-10px]">
        <div class="relative before:content-[''] before:absolute before:inset-0 before:rounded-full before:border-[4px] before:border-black before:animate-pulse before:z-0">
          <img src="assets/img/librarian.jpg" alt="Library Manager"
               class="relative z-10 rounded-full w-32 h-32 object-cover border-4 border-white shadow-md">
        </div>

        <div class="relative before:content-[''] before:absolute before:inset-0 before:rounded-full before:border-[4px] before:border-blue-400 before:animate-ping before:z-0 mt-[-10px]">
          <img src="assets/img/l1.jpg" alt="Library"
               class="relative z-10 rounded-full w-28 h-28 object-cover border-4 border-white shadow-md">
        </div>
      </div>
    </div>
  </section>
    <section id="about" class="min-h-screen w-full px-6 py-16 bg-gradient-to-b from-[#e6fff7] to-[#fffaf0] flex flex-col items-center justify-center text-center">
  <h2 class="text-4xl font-bold text-[#048C9E] mb-6 font-[Plus Jakarta Sans]">
   A Space Designed for Your Focus
  </h2>
  <p class="max-w-2xl text-lg text-[#333] mb-12">
   Relax in our fully air-conditioned library with quiet fans, cozy seating, and a perfect atmosphere for focused reading.
  </p>

   
  <div class="w-full max-w-7xl flex flex-col lg:flex-row justify-center items-center gap-8">
    

  <div class="bg-white shadow-xl rounded-xl p-4 w-full max-w-sm flex flex-col items-center">
      <img src="assets/img/seat.jpeg" alt="Library Resource 2" class="w-full h-48 object-cover rounded-lg mb-4">
      <h3 class="text-xl font-semibold text-[#048C9E]">
  <a href="./login.php" class="inline-block bg-[#048C9E] text-white px-5 py-2 rounded-lg shadow-md hover:bg-[#036d79] transition">
    Book Now
  </a>
</h3>

      <p class="text-gray-600 text-sm mt-2">Secure your place in the library with our easy seat booking system — no more searching, just studying.</p>
    </div>
    

     <div class="bg-white shadow-xl rounded-xl p-4 w-full max-w-sm flex flex-col items-center">
      <img src="assets/img/l2.jpeg" alt="Library Resource 1" class="w-full h-48 object-cover rounded-lg mb-4">
      <h3 class="text-xl font-semibold text-[#048C9E]">Library</h3>
      <p class="text-gray-600 text-sm mt-2">Chill with cool AC, soft fans, and a calm vibe — your perfect corner for books and beyond.</p>
    </div>
 
    <div class="bg-white shadow-xl rounded-xl p-4 w-full max-w-sm flex flex-col items-center">
      <img src="assets/img/multi.jpeg" alt="Library Resource 3" class="w-full h-48 object-cover rounded-lg mb-4">
      <h3 class="text-xl font-semibold text-[#048C9E]">Connect & Explore</h3>
      <p class="text-gray-600 text-sm mt-2">Access free Wi-Fi across the library and power up your learning with uninterrupted internet.</p>
    </div>

  </div>
</section>


   
  <style>
    @keyframes typing {
      from { width: 0 }
      to { width: 100% }
    }
    @keyframes blink {
      0%, 100% { border-color: transparent }
      50% { border-color: #131e5a }
    }
    .animate-typing {
      animation: typing 4s steps(60, end), blink 0.7s step-end infinite;
      white-space: nowrap;
      overflow: hidden;
      display: inline-block;
      border-right: 2px solid;
    }
  </style>

  <footer class="absolute bottom-0 w-full text-center py-3 text-gray-400 text-sm z-10">
    &copy; <?= date('Y'); ?> Digital Study Library. All rights reserved.
  </footer>
</body>
</html>
