(function () {
  const inputIn  = document.getElementById('check_in');
  const inputOut = document.getElementById('check_out');
  if (!inputIn || !inputOut) return;

  const periods = (window.BOOKED_PERIODS || []).map(p => ({
    in:  new Date(p.check_in  + 'T00:00:00'),
    out: new Date(p.check_out + 'T00:00:00')
  }));

  const parseRu = (s) => {
    const m = s.match(/^(\d{2})\.(\d{2})\.(\d{4})$/);
    return m ? new Date(+m[3], +m[2] - 1, +m[1]) : null;
  };

  const overlaps = (aIn, aOut, bIn, bOut) => !(aOut <= bIn || aIn >= bOut);

  const validate = () => {
    const ci = parseRu(inputIn.value);
    const co = parseRu(inputOut.value);
    const today = new Date(); today.setHours(0,0,0,0);

    inputIn.classList.remove('invalid');
    inputOut.classList.remove('invalid');
    inputIn.setCustomValidity('');
    inputOut.setCustomValidity('');

    if (ci && ci < today) {
      inputIn.classList.add('invalid');
      inputIn.setCustomValidity('Дата заезда не раньше сегодня');
    }

    if (ci && co && co <= ci) {
      inputOut.classList.add('invalid');
      inputOut.setCustomValidity('Дата выезда позже заезда');
    }

    if (ci && co) {
      const busy = periods.some(p => overlaps(ci, co, p.in, p.out));
      if (busy) {
        inputIn.classList.add('invalid');
        inputIn.setCustomValidity('Период занят — выберите другие даты');
      }
    }
    
    if (!inputIn.checkValidity()) inputIn.reportValidity();
    else if (!inputOut.checkValidity()) inputOut.reportValidity();
  };

  inputIn.addEventListener('change', validate);
  inputOut.addEventListener('change', validate);
  inputIn.addEventListener('blur', validate);
  inputOut.addEventListener('blur', validate);
})();