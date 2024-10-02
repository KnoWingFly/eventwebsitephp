<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up & Log In</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-300 font-sans">
<div class="max-w-lg mx-auto mt-10 bg-gray-800 rounded-lg shadow-lg">
  <ul class="flex justify-center mb-6">
    <li class="w-1/2 text-center">
      <a href="#signup" class="block py-3 text-white bg-teal-500 rounded-tl-lg">Sign Up</a>
    </li>
    <li class="w-1/2 text-center">
      <a href="#login" class="block py-3 text-gray-400 hover:bg-teal-500 hover:text-white transition">Log In</a>
    </li>
  </ul>

  <div class="p-6 tab-content">
    <div id="signup" class="block">
      <h1 class="text-3xl text-center text-white mb-6">Sign Up for Free</h1>
      <form action="/register" method="post">
        <div class="flex gap-4">
          <div class="mt-6">
            <label class="text-gray-400">Name<span class="text-red-500">*</span></label>
            <input type="text" name="name" required autocomplete="off" class="w-full p-3 mt-2 bg-gray-700 text-white border border-gray-600 rounded focus:outline-none focus:border-teal-500">
          </div>
        </div>
        <div class="mt-6">
          <label class="text-gray-400">Email Address<span class="text-red-500">*</span></label>
          <input type="email" name="email" required autocomplete="off" class="w-full p-3 mt-2 bg-gray-700 text-white border border-gray-600 rounded focus:outline-none focus:border-teal-500">
        </div>
        <div class="mt-6">
          <label class="text-gray-400">Set A Password<span class="text-red-500">*</span></label>
          <input type="password" name="password" required autocomplete="off" class="w-full p-3 mt-2 bg-gray-700 text-white border border-gray-600 rounded focus:outline-none focus:border-teal-500">
        </div>
        <div class="mt-6">
          <label class="text-gray-400">Confirm Password<span class="text-red-500">*</span></label>
          <input type="password" name="confirm_password" required autocomplete="off" class="w-full p-3 mt-2 bg-gray-700 text-white border border-gray-600 rounded focus:outline-none focus:border-teal-500">
        </div>
        <button type="submit" class="mt-6 w-full py-3 bg-teal-500 text-white rounded-lg hover:bg-teal-600">Get Started</button>
      </form>
    </div>

    <div id="login" class="hidden">
      <h1 class="text-3xl text-center text-white mb-6">Welcome Back!</h1>
      <form action="/login" method="post">
        <div class="mt-6">
          <label class="text-gray-400">Email Address<span class="text-red-500">*</span></label>
          <input type="email" name="email" required autocomplete="off" class="w-full p-3 mt-2 bg-gray-700 text-white border border-gray-600 rounded focus:outline-none focus:border-teal-500">
        </div>
        <div class="mt-6">
          <label class="text-gray-400">Password<span class="text-red-500">*</span></label>
          <input type="password" name="password" required autocomplete="off" class="w-full p-3 mt-2 bg-gray-700 text-white border border-gray-600 rounded focus:outline-none focus:border-teal-500">
        </div>
        <button type="submit" class="mt-6 w-full py-3 bg-teal-500 text-white rounded-lg hover:bg-teal-600">Log In</button>
      </form>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
  $('ul li a').on('click', function(e) {
    e.preventDefault(); 
    
    var target = $(this).attr('href');

    if ($(target).is(':visible')) {
      return;
    }

    $('.tab-content > div:visible').fadeOut(300, function() {
      $(target).fadeIn(300);
    });

    $(this).addClass('bg-teal-500 text-white').removeClass('text-gray-400');
    $(this).parent().siblings().find('a').addClass('text-gray-400').removeClass('bg-teal-500 text-white');
  });
});
</script>
</body>
</html>
