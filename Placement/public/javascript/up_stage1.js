let combinaisonsCourantes = [];

function champsDeBaseComplets() {
    const date = document.getElementById('date_devoir')?.value;
    const heure = document.getElementById('heure_debut')?.value;
    const duree = document.getElementById('duree')?.value;
    return Boolean(date && heure && duree);
}

function selectionCombiComplete() {
    const promo = document.getElementById('sel_promo')?.value;
    const salle = document.getElementById('sel_salle')?.value;
    const mat = document.getElementById('sel_matiere')?.value;
    return Boolean(promo && salle && mat);
}

function updateSubmitState() {
    const btnSuivant = document.getElementById('btnSuivant');
    if (!btnSuivant) return;
    const combiPretes = combinaisonsCourantes.length > 0;
    const peutSoumettre =
        champsDeBaseComplets() &&
        (selectionCombiComplete() || combiPretes);
    btnSuivant.disabled = !peutSoumettre;
}

// Fetch groupe list when promo changes
async function grDynamique() {
    const idPromo = document.getElementById('sel_promo').value;
    const selGroupe = document.getElementById('sel_groupe');
    const selMatiere = document.getElementById('sel_matiere');
    const btnAdd = document.getElementById('btnAddCombi');

    if (!idPromo) {
        selGroupe.innerHTML = '<option value="0">Toute la promo</option>';
        selMatiere.innerHTML = '<option value="">-- Matière --</option>';
        selGroupe.style.display = 'none';
        selMatiere.style.display = 'none';
        btnAdd.style.display = 'none';
        updateSubmitState();
        return;
    }

    try {
        const resp = await fetch('index.php?action=ajax_groupe', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'idPromo=' + encodeURIComponent(idPromo)
        });
        const data = await resp.json();

        // Repopulate sel_groupe
        selGroupe.innerHTML = '<option value="0">Toute la promo</option>';
        data.forEach(g => {
            selGroupe.innerHTML += '<option value="' + g.id_groupe + '">' + escHtml(g.nom_groupe) + '</option>';
        });
        selGroupe.style.display = '';

        await matDynamique();
        affBtn();
    } catch (e) {
        alert('Impossible de charger les groupes pour cette promotion.');
    }
}

async function matDynamique() {
    const idPromo = document.getElementById('sel_promo').value;
    const sel = document.getElementById('sel_matiere');

    try {
        const resp = await fetch('index.php?action=ajax_matiere', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'idPromo=' + encodeURIComponent(idPromo)
        });
        const data = await resp.json();

        sel.innerHTML = '<option value="">-- Matière --</option>';
        data.forEach(m => {
            sel.innerHTML += '<option value="' + m.id_mat + '">' + escHtml(m.nom_mat) + '</option>';
        });
        sel.style.display = '';
    } catch (e) {
        sel.innerHTML = '<option value="">-- Matière --</option>';
        sel.style.display = 'none';
        alert('Impossible de charger les matières pour cette promotion.');
    }

    sel.onchange = affBtn;
    updateSubmitState();
}

function affBtn() {
    const btn = document.getElementById('btnAddCombi');
    if (btn) btn.style.display = 'none';
    updateSubmitState();
}

async function recupCombi() {
    const idPromo = document.getElementById('sel_promo').value;
    const idGroupe = document.getElementById('sel_groupe').value || 0;
    const idSalle = document.getElementById('sel_salle').value;
    const idMat = document.getElementById('sel_matiere').value;

    try {
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
            renderCombi(data.combinaisons || []);
            return false;
        }
        renderCombi(data.combinaisons || []);
        return true;
    } catch (e) {
        alert('Erreur réseau lors de l\'ajout de la combinaison.');
        return false;
    }
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
    combinaisonsCourantes = combinaisons;
    const div = document.getElementById('tabRecap');
    if (!combinaisons.length) {
        div.innerHTML = '';
        updateSubmitState();
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
    updateSubmitState();
}

function escHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

async function restaurerDerniereSelection() {
    const form = document.getElementById('formStage1');
    if (!form) return;

    const lastPromo = form.dataset.lastPromo || '';
    const lastGroupe = form.dataset.lastGroupe || '0';
    const lastSalle = form.dataset.lastSalle || '';
    const lastMatiere = form.dataset.lastMatiere || '';

    const selPromo = document.getElementById('sel_promo');
    const selGroupe = document.getElementById('sel_groupe');
    const selMatiere = document.getElementById('sel_matiere');
    const selSalle = document.getElementById('sel_salle');

    if (lastSalle && selSalle) {
        selSalle.value = lastSalle;
    }

    if (!lastPromo || !selPromo) {
        updateSubmitState();
        return;
    }

    selPromo.value = lastPromo;
    await grDynamique();

    if (selGroupe) {
        selGroupe.value = lastGroupe;
    }
    if (selMatiere) {
        selMatiere.value = lastMatiere;
    }
    updateSubmitState();
}

// Load existing combinaisons on page load
(async function () {
    try {
        const resp = await fetch('index.php?action=ajax_affiche_combi', {method: 'POST'});
        const data = await resp.json();
        renderCombi(data.combinaisons || []);
    } catch (e) {
        renderCombi([]);
    }
    await restaurerDerniereSelection();
})();

document.getElementById('formStage1')?.addEventListener('submit', function (e) {
    if (!combinaisonsCourantes.length && !selectionCombiComplete()) {
        e.preventDefault();
        alert('Merci de choisir une promotion, un groupe, une matière et une salle.');
    }
});

['date_devoir', 'heure_debut', 'duree', 'sel_salle', 'sel_promo', 'sel_groupe', 'sel_matiere'].forEach(function (id) {
    const el = document.getElementById(id);
    if (el) {
        el.addEventListener('change', updateSubmitState);
        el.addEventListener('input', updateSubmitState);
    }
});

updateSubmitState();
