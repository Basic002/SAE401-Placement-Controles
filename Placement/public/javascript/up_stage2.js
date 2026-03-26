var selectedCells = [];

function selecTwo(cell) {
    const etuId = cell.getAttribute('data-etu-id');
    if (!etuId) return; // empty cell, skip

    // If already selected, deselect
    if (selectedCells.includes(cell)) {
        cell.classList.remove('placeSelec');
        selectedCells = selectedCells.filter(c => c !== cell);
        if (selectedCells.length < 2) {
            document.getElementById('btnInter').style.display = 'none';
        }
        return;
    }

    if (selectedCells.length >= 2) {
        // Reset all
        selectedCells.forEach(c => {
            c.classList.remove('placeSelec');
        });
        selectedCells = [];
        document.getElementById('btnInter').style.display = 'none';
    }

    cell.classList.add('placeSelec');
    selectedCells.push(cell);

    if (selectedCells.length === 2) {
        document.getElementById('btnInter').style.display = '';
    }
}

async function intervertir() {
    if (selectedCells.length !== 2) return;

    const etu1 = selectedCells[0].getAttribute('data-etu-id');
    const etu2 = selectedCells[1].getAttribute('data-etu-id');

    const resp = await fetch('index.php?action=ajax_intervertir', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'etu1=' + encodeURIComponent(etu1) + '&etu2=' + encodeURIComponent(etu2)
    });
    const data = await resp.json();

    if (!data.ok) {
        alert(data.message || 'Erreur lors de l\'interversion.');
        return;
    }

    // Swap DOM: swap innerHTML and data-etu-id between the two cells
    const html0 = selectedCells[0].innerHTML;
    const id0 = selectedCells[0].getAttribute('data-etu-id');

    selectedCells[0].innerHTML = selectedCells[1].innerHTML;
    selectedCells[0].setAttribute('data-etu-id', selectedCells[1].getAttribute('data-etu-id') || '');

    selectedCells[1].innerHTML = html0;
    selectedCells[1].setAttribute('data-etu-id', id0 || '');

    // Reset selection
    selectedCells.forEach(c => c.classList.remove('placeSelec'));
    selectedCells = [];
    document.getElementById('btnInter').style.display = 'none';
}
