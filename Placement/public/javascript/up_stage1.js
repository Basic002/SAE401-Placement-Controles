// Fetch groupe list when promo changes
async function grDynamique() {
    const idPromo = document.getElementById('sel_promo').value;
    if (!idPromo) return;

    const resp = await fetch('index.php?action=ajax_groupe', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'idPromo=' + encodeURIComponent(idPromo)
    });
    const data = await resp.json();

    // Repopulate sel_groupe
    const sel = document.getElementById('sel_groupe');
    sel.innerHTML = '<option value="0">Toute la promo</option>';
    data.forEach(g => {
        sel.innerHTML += '<option value="' + g.id_groupe + '">' + escHtml(g.nom_groupe) + '</option>';
    });
    sel.style.display = '';

    await matDynamique();
    affBtn();
}

async function matDynamique() {
    const idPromo = document.getElementById('sel_promo').value;
    const resp = await fetch('index.php?action=ajax_matiere', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'idPromo=' + encodeURIComponent(idPromo)
    });
    const data = await resp.json();

    const sel = document.getElementById('sel_matiere');
    sel.innerHTML = '<option value="">-- Matière --</option>';
    data.forEach(m => {
        sel.innerHTML += '<option value="' + m.id_mat + '">' + escHtml(m.nom_mat) + '</option>';
    });
    sel.style.display = '';
    sel.onchange = affBtn;
}

function affBtn() {
    const promo = document.getElementById('sel_promo').value;
    const salle = document.getElementById('sel_salle').value;
    const mat = document.getElementById('sel_matiere');
    const btn = document.getElementById('btnAddCombi');
    if (promo && salle && mat && mat.value) {
        btn.style.display = '';
    } else {
        btn.style.display = 'none';
    }
}

async function recupCombi() {
    const idPromo = document.getElementById('sel_promo').value;
    const idGroupe = document.getElementById('sel_groupe').value || 0;
    const idSalle = document.getElementById('sel_salle').value;
    const idMat = document.getElementById('sel_matiere').value;

    const resp = await fetch('index.php?action=ajax_add_combi', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'idPromo=' + encodeURIComponent(idPromo)
            + '&idGroupe=' + encodeURIComponent(idGroupe)
            + '&idSalle=' + encodeURIComponent(idSalle)
            + '&idMat=' + encodeURIComponent(idMat)
    });
    const data = await resp.json();

    if (!data.ok) {
        alert(data.message || 'Erreur lors de l\'ajout.');
    }
    renderCombi(data.combinaisons || []);
}

async function supprCombi(index) {
    const resp = await fetch('index.php?action=ajax_suppr_combi', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'index=' + encodeURIComponent(index)
    });
    const data = await resp.json();
    renderCombi(data.combinaisons || []);
}

function renderCombi(combinaisons) {
    const div = document.getElementById('tabRecap');
    if (!combinaisons.length) {
        div.innerHTML = '';
        document.getElementById('btnSuivant').disabled = true;
        return;
    }
    let html = '<table><tr><th>Promo/Groupe</th><th>Salle</th><th>Matière</th><th>Étudiants</th><th></th></tr>';
    combinaisons.forEach((c, i) => {
        html += '<tr>'
            + '<td>' + escHtml(c.label_promo) + '</td>'
            + '<td>' + escHtml(c.nom_salle) + '</td>'
            + '<td>' + escHtml(c.nom_mat) + '</td>'
            + '<td>' + c.nb_etud + '</td>'
            + '<td><button type="button" onclick="supprCombi(' + i + ')">&#x2715;</button></td>'
            + '</tr>';
    });
    html += '</table>';
    div.innerHTML = html;
    document.getElementById('btnSuivant').disabled = false;
}

function escHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

// Load existing combinaisons on page load
(async function () {
    const resp = await fetch('index.php?action=ajax_affiche_combi', {method: 'POST'});
    const data = await resp.json();
    renderCombi(data.combinaisons || []);
})();
