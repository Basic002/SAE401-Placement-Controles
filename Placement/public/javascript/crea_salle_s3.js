/* Éditeur de grille — Étape 3 création salle */

var classMap = {
    0: 'couloir',
    1: 'placeOk',
    2: 'placeHandi',
    3: 'placeInex'
};

var digitMap = {
    'couloir':    0,
    'placeOk':    1,
    'placeHandi': 2,
    'placeInex':  3
};

/**
 * Returns the currently selected cell type value (0-3).
 */
function recupChoix() {
    var radios = [
        document.getElementById('radio_couloir'),
        document.getElementById('radio_placeOk'),
        document.getElementById('radio_handi'),
        document.getElementById('radio_inex')
    ];
    for (var i = 0; i < radios.length; i++) {
        if (radios[i] && radios[i].checked) {
            return parseInt(radios[i].value, 10);
        }
    }
    return 1; // fallback: placeOk
}

/**
 * Called on cell click. Updates the cell's CSS class based on the selected radio.
 * No AJAX — pure DOM update.
 */
function modifEtat(cell) {
    var choix = recupChoix();
    var nouvelleClasse = classMap[choix];
    if (nouvelleClasse !== undefined) {
        cell.className = nouvelleClasse;
    }
}

/**
 * Builds the donnee string from current cell classes and writes it into the
 * hidden #donnee input.
 * Format: for each row, concatenate the digit for each cell, then append '-'.
 * Example: "111-010-111-"
 */
function buildDonnee() {
    var table = document.getElementById('TAB1');
    if (!table) { return ''; }

    var result = '';
    var rows = table.rows;

    for (var i = 0; i < rows.length; i++) {
        var cells = rows[i].cells;
        for (var j = 0; j < cells.length; j++) {
            var cls = cells[j].className;
            var digit = (digitMap[cls] !== undefined) ? digitMap[cls] : 1;
            result += digit;
        }
        result += '-';
    }

    return result;
}
