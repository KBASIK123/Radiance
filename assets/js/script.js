// Функция для бронирования номера
document.addEventListener("DOMContentLoaded", function () {
  // Обработка формы бронирования
  const bookingForm = document.getElementById("bookingForm");
  if (bookingForm) {
    bookingForm.addEventListener("submit", function (e) {
      e.preventDefault();

      const checkIn = new Date(document.getElementById("check_in").value);
      const checkOut = new Date(document.getElementById("check_out").value);

      // Проверка, что дата выезда после даты заезда
      if (checkOut <= checkIn) {
        alert("Дата выезда должна быть позже даты заезда");
        return;
      }

      // Проверка минимального срока бронирования (1 ночь)
      const nights = (checkOut - checkIn) / (1000 * 60 * 60 * 24);
      if (nights < 1) {
        alert("Минимальный срок бронирования - 1 ночь");
        return;
      }

      // Если все проверки пройдены, отправляем форму
      this.submit();
    });
  }

  // Инициализация календарей
  const today = new Date();
  const tomorrow = new Date(today);
  tomorrow.setDate(tomorrow.getDate() + 1);

  // Установка минимальных дат для календарей
  document.getElementById("check_in").min = today.toISOString().split("T")[0];
  document.getElementById("check_out").min = tomorrow
    .toISOString()
    .split("T")[0];

  // Обновление минимальной даты выезда при изменении даты заезда
  document.getElementById("check_in").addEventListener("change", function () {
    const checkIn = new Date(this.value);
    const minCheckOut = new Date(checkIn);
    minCheckOut.setDate(minCheckOut.getDate() + 1);

    document.getElementById("check_out").min = minCheckOut
      .toISOString()
      .split("T")[0];

    // Если текущая дата выезда раньше новой минимальной, сбросить её
    if (new Date(document.getElementById("check_out").value) < minCheckOut) {
      document.getElementById("check_out").value = minCheckOut
        .toISOString()
        .split("T")[0];
    }
  });

  // Анимация цен
  const prices = document.querySelectorAll(".price");
  prices.forEach((price) => {
    price.addEventListener("mouseover", function () {
      this.style.transform = "scale(1.05)";
    });

    price.addEventListener("mouseout", function () {
      this.style.transform = "scale(1)";
    });
  });

  // Галерея
  const galleryItems = document.querySelectorAll(".gallery-item");
  galleryItems.forEach((item) => {
    item.addEventListener("click", function () {
      const imgSrc = this.querySelector("img").src;
      const imgAlt = this.querySelector("img").alt;
      openModal(imgSrc, imgAlt);
    });
  });
});

// Функции для модальных окон
function openModal(src, title) {
  const modal = document.getElementById("modal");
  const modalImg = document.getElementById("modal-img");
  const caption = document.getElementById("caption");

  modal.style.display = "block";
  modalImg.src = src;
  caption.textContent = title;
}

function closeModal() {
  document.getElementById("modal").style.display = "none";
}

// Закрытие модального окна при клике вне изображения
window.onclick = function (event) {
  const modal = document.getElementById("modal");
  if (event.target == modal) {
    modal.style.display = "none";
  }
};
document.addEventListener("DOMContentLoaded", function () {
  const filterBtns = document.querySelectorAll(".filter-btn");
  const galleryItems = document.querySelectorAll(".gallery-item");

  filterBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      // Удаляем активный класс у всех кнопок
      filterBtns.forEach((b) => b.classList.remove("active"));
      // Добавляем активный класс текущей кнопке
      this.classList.add("active");

      const filter = this.dataset.filter;

      galleryItems.forEach((item) => {
        if (filter === "all" || item.dataset.category === filter) {
          item.style.display = "block";
        } else {
          item.style.display = "none";
        }
      });
    });
  });
});

