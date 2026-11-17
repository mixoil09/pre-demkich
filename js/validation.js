document.addEventListener('DOMContentLoaded', () => {
  /* ================= Каталог: фильтр ================= */
  const filterForm = document.getElementById('filterForm');
  const applyBtn  = document.getElementById('applyFilter');
  const resetBtn  = document.getElementById('resetFilter');
  const rooms     = document.querySelectorAll('.room');

  if (applyBtn) {
    applyBtn.addEventListener('click', () => {
      const category = document.getElementById('filterCategory').value;
      rooms.forEach(room => {
        const match = !category || room.dataset.category === category;
        room.style.display = match ? '' : 'none';
      });
    });
  }
  if (resetBtn) {
    resetBtn.addEventListener('click', () => {
      document.getElementById('filterCategory').value = '';
      rooms.forEach(r => r.style.display = '');
    });
  }

  /* ================= Бронирование: форма ================= */
  const form = document.getElementById('bookingForm');
  if (!form) return;

  // Выбранный номер из URL
  const params = new URLSearchParams(location.search);
  const roomId = params.get('room');
  const info = document.getElementById('roomInfo');
  if (roomId && info) info.textContent = `Вы бронируете номер #${roomId}`;

  const setError = (name, msg) => {
    const input = form.elements[name];
    const err = form.querySelector(`[data-error-for="${name}"]`);
    if (input) input.classList.toggle('invalid', !!msg);
    if (err) err.textContent = msg || '';
  };

  const nameRe  = /^[А-Яа-яЁё\s.\-]+$/;
  const phoneRe = /^\+7\(\d{3}\)\d{3}-\d{2}-\d{2}$/;
  const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  const dateRe  = /^(\d{2})\.(\d{2})\.(\d{4})$/;

  const parseDate = (str) => {
    const m = str.match(dateRe);
    if (!m) return null;
    const [, d, mo, y] = m;
    const dt = new Date(+y, +mo - 1, +d);
    return (dt.getDate() === +d && dt.getMonth() === +mo - 1 && dt.getFullYear() === +y)
      ? dt : null;
  };

  const validate = () => {
    let ok = true;

    // Имя
    const firstName = form.elements.firstName.value.trim();
    if (!firstName) { setError('firstName', 'Укажите имя'); ok = false; }
    else if (!nameRe.test(firstName)) { setError('firstName', 'Только кириллица, пробелы, точки, тире'); ok = false; }
    else setError('firstName', '');

    // Фамилия
    const lastName = form.elements.lastName.value.trim();
    if (!lastName) { setError('lastName', 'Укажите фамилию'); ok = false; }
    else if (!nameRe.test(lastName)) { setError('lastName', 'Только кириллица, пробелы, точки, тире'); ok = false; }
    else setError('lastName', '');

    // Телефон
    const phone = form.elements.phone.value.trim();
    if (!phone) { setError('phone', 'Укажите телефон'); ok = false; }
    else if (!phoneRe.test(phone)) { setError('phone', 'Формат: +7(XXX)XXX-XX-XX'); ok = false; }
    else setError('phone', '');

    // E-mail
    const email = form.elements.email.value.trim();
    if (!email) { setError('email', 'Укажите e-mail'); ok = false; }
    else if (!emailRe.test(email)) { setError('email', 'Некорректный e-mail'); ok = false; }
    else setError('email', '');

    // Даты
    const checkIn  = parseDate(form.elements.checkIn.value.trim());
    const checkOut = parseDate(form.elements.checkOut.value.trim());
    const today = new Date(); today.setHours(0,0,0,0);

    if (!checkIn) { setError('checkIn', 'Формат: ДД.ММ.ГГГГ'); ok = false; }
    else if (checkIn < today) { setError('checkIn', 'Дата заезда не раньше сегодня'); ok = false; }
    else setError('checkIn', '');

    if (!checkOut) { setError('checkOut', 'Формат: ДД.ММ.ГГГГ'); ok = false; }
    else if (!checkIn || checkOut <= checkIn) { setError('checkOut', 'Дата выезда позже даты заезда'); ok = false; }
    else setError('checkOut', '');

    return ok;
  };

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    if (!validate()) return;

    // Прототип: показываем сообщение об успехе (серверная отправка — в задании 3)
    document.getElementById('successMessage').hidden = false;
    form.reset();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  // Живая проверка при вводе
  form.addEventListener('input', (e) => {
    const name = e.target.name;
    if (['firstName','lastName','phone','email','checkIn','checkOut'].includes(name)) {
      validate();
    }
  });
});
