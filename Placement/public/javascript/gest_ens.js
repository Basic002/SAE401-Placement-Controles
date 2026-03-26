(function () {
    var btncrea = document.getElementById('btncrea');
    var bloccreaens = document.getElementById('bloccreaens');
    if (btncrea && bloccreaens) {
        btncrea.addEventListener('click', function () {
            bloccreaens.style.display = 'block';
        }, false);
    }
})();
