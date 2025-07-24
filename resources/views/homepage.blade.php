<!doctype html>
<html>
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    @vite('resources/css/app.css')
    <title>Paws & Preferences: Find Your Favourite Kitty</title>
  </head>
  <body class="bg-gray-100 min-h-screen flex flex-col items-center justify-start p-4 bg-cover bg-center" style="background-image: url('/images/bg.jpg'); background-size: cover; background-position: center 100%; background-repeat: no-repeat;">
    <h1 class="text-3xl font-bold text-center mt-4 mb-2 text-stone-600">Paws & Preferences</h1>
    <p class="text-center text-gray-600 mb-4">Swipe right if you like the kitty, left if you don't!</p>

    <!-- Cat image stack in a container -->
    <div id="cat-stack" class="w-full max-w-xs mx-auto flex flex-col items-center justify-center min-h-[350px] bg-white rounded-lg shadow-md overflow-hidden mb-6">
      <!-- Cat image -->
      <!-- Buttons for desktop users -->
      <div id="swipe-buttons" class="flex w-full mt-0 overflow-hidden shadow-lg">
        <button id="dislike-btn" class="w-1/2 bg-red-700 text-white px-0 py-3 text-lg font-bold hover:bg-red-600 focus:bg-red-700 focus:outline-none border-r border-white">Dislike</button>
        <button id="like-btn" class="w-1/2 bg-green-700 text-white px-0 py-3 text-lg font-bold hover:bg-green-600 focus:bg-green-700 focus:outline-none border-l border-white">Like</button>
      </div>
      <!-- Summary of liked cats -->
    </div>

    <!-- Modal for full-size cat image -->
    <div id="cat-modal" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 hidden">
      <div class="relative bg-white rounded-lg shadow-lg p-2 max-w-xs w-full flex flex-col items-center">
        <button id="close-modal" class="absolute top-2 right-2 text-gray-700 text-2xl font-bold">&times;</button>
        <img id="modal-img" src="" alt="Cat" class="max-w-full max-h-[70vh] rounded" />
      </div>
    </div>

    <script>
      // fixed number of cat images to fetch to 10 only
      const CAT_COUNT = 10;
      const catStack = document.getElementById('cat-stack');
      const summaryCount = document.createElement('p');
      summaryCount.id = 'summary-count';
      summaryCount.className = 'mb-2';
      const likedCatsDiv = document.createElement('div');
      likedCatsDiv.id = 'liked-cats';
      likedCatsDiv.className = 'flex flex-wrap gap-2 mb-4';
      const restartBtn = document.createElement('button');
      restartBtn.id = 'restart-btn';
      restartBtn.className = 'w-full bg-zinc-700 text-white py-2 rounded font-bold hover:bg-zinc-900';
      restartBtn.textContent = 'Restart';

      restartBtn.addEventListener('click', () => {
        // Remove summary and restore cat image
        const heading = document.getElementById('summary-heading');
        if (heading) heading.remove();
        const summaryCountEl = document.getElementById('summary-count');
        if (summaryCountEl) summaryCountEl.remove();
        const likedCatsGrid = document.getElementById('liked-cats');
        if (likedCatsGrid) likedCatsGrid.remove();
        const restartBtnEl = document.getElementById('restart-btn');
        if (restartBtnEl) restartBtnEl.remove();
        currentIndex = 0;
        likedCats = [];
        document.getElementById('swipe-buttons').style.display = 'flex';
        closeModal();
        fetchCatImages();
        attachButtonListeners();
      });
      let catImages = []; // store cat image URLs
      let currentIndex = 0; // track current cat image
      let likedCats = []; // store URL of liked cats

      // fetching cat images from Cataas
      async function fetchCatImages() {
        const urls = []; // store cat image URLs
        for (let i = 0; i < CAT_COUNT; i++) {
          // Add a unique query param to avoid caching and duplication
          urls.push(`https://cataas.com/cat?width=350&height=350&v=${Date.now()}_${i}_${Math.random().toString(36).slice(2)}`);
        }
        catImages = urls;
        showCat(currentIndex);
        attachButtonListeners();
      }

      // display cat images one by one
      function showCat(index) {
        // remove the cat image and not the swipe buttons
        catStack.querySelectorAll('img').forEach(img => img.remove());
        if (catImages[index]) {
          const img = document.createElement('img');
          img.src = catImages[index];
          img.alt = 'Cute cat';
          img.className = 'w-full h-[350px] object-cover transition-transform duration-300';
          img.style.transform = '';
          catStack.insertBefore(img, catStack.firstChild);
        }
        // Hide buttons if finished
        document.getElementById('swipe-buttons').style.display = (index < CAT_COUNT) ? 'flex' : 'none';
      }

      // handle swipe logic
      function handleSwipe(liked, animate = true) {
        const img = catStack.querySelector('img');
        if (img && animate) {
          img.style.transition = 'transform 0.3s';
          img.style.transform = liked ? 'translateX(400px) rotate(15deg)' : 'translateX(-400px) rotate(-15deg)'; // like = right, dislike = left
          setTimeout(() => {
            finishSwipe(liked);
          }, 300);
        } else {
          finishSwipe(liked);
        }
      }

      // handle swipe logic
      function finishSwipe(liked) {
        if (liked && catImages[currentIndex]) {
          likedCats.push(catImages[currentIndex]);
        }
        currentIndex++;
        if (currentIndex < CAT_COUNT) {
          showCat(currentIndex);
        } else {
          showSummary();
        }
      }

      function showSummary() {
        // Only remove the cat image, not the swipe buttons
        catStack.querySelectorAll('img').forEach(img => img.remove());
        document.getElementById('swipe-buttons').style.display = 'none';
        // Heading
        const heading = document.createElement('h2');
        heading.id = 'summary-heading';
        heading.className = 'text-xl font-bold text-stone-600 mb-2 mt-4';
        heading.textContent = 'Your Favourite Cats!';
        // Count
        summaryCount.textContent = `You liked ${likedCats.length} out of ${CAT_COUNT} cats!`;
        summaryCount.className = 'mb-4 text-gray-700';
        // Liked cats grid
        likedCatsDiv.className = 'grid grid-cols-3 gap-3 mb-6';
        likedCatsDiv.innerHTML = '';
        likedCats.forEach(url => {
          const img = document.createElement('img');
          img.src = url;
          img.alt = 'Liked cat';
          img.className = 'w-20 h-20 object-cover rounded-lg shadow hover:scale-110 transition-transform cursor-pointer border-2 border-white hover:border-blue-400';
          img.addEventListener('click', () => openModal(url));
          likedCatsDiv.appendChild(img);
        });
        // Restart button
        restartBtn.className = 'w-3xs mb-4 mx-auto bg-zinc-700 text-white py-3 font-bold shadow-lg hover:bg-zinc-900 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-yellow-400 transition-all duration-200';
        // Append summary elements directly to catStack
        catStack.appendChild(heading);
        catStack.appendChild(summaryCount);
        catStack.appendChild(likedCatsDiv);
        catStack.appendChild(restartBtn);
      }

      // Modal logic
      const catModal = document.getElementById('cat-modal');
      const modalImg = document.getElementById('modal-img');
      const closeModalBtn = document.getElementById('close-modal');

      function openModal(url) {
        modalImg.src = url;
        catModal.classList.remove('hidden');
      }
      function closeModal() {
        catModal.classList.add('hidden');
        modalImg.src = '';
      }
      closeModalBtn.addEventListener('click', closeModal);
      catModal.addEventListener('click', function(e) {
        if (e.target === catModal) closeModal();
      });

      // Touch event logic for swipe (mobile)
      let touchStartX = null;
      catStack.addEventListener('touchstart', function(e) {
        if (e.touches.length === 1) {
          touchStartX = e.touches[0].clientX;
        }
      });
      catStack.addEventListener('touchend', function(e) {
        if (touchStartX === null) return;
        const touchEndX = e.changedTouches[0].clientX;
        const diffX = touchEndX - touchStartX;
        if (Math.abs(diffX) > 50) {
          if (diffX > 0) {
            // Swipe right = like
            handleSwipe(true);
          } else {
            // Swipe left = dislike
            handleSwipe(false);
          }
        }
        touchStartX = null;
      });

      // attach event listeners to buttons
      function attachButtonListeners() {
        const likeBtn = document.getElementById('like-btn');
        const dislikeBtn = document.getElementById('dislike-btn');
        if (likeBtn && dislikeBtn) {
          likeBtn.onclick = () => handleSwipe(true);
          dislikeBtn.onclick = () => handleSwipe(false);
        }
      }

      // fetch cats when page loads
      fetchCatImages();
      attachButtonListeners();
    </script>
  </body>
</html>