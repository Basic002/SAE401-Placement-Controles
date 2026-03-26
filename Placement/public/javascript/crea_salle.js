// ############ Gestion boutons Precedent/Suivant ############
var stageContainer = document.getElementById('stage-content');
var currentStageInput = document.getElementById('currentStageName');

var btnbef=document.getElementById('btnbef');
var btnnext=document.getElementById('btnnext');
var btnsave=document.getElementById('btnsave');

function getTooltip(element)
{
    while (element=element.nextSibling)
    {
        if (element.className==='tooltip')
        {
            return element;
        }
    }
    return false;
}


function checkChamp()
{
	var nomSalle=document.getElementById("nomSalle");
	var rangSalle=document.getElementById("nbRang");
	var colSalle=document.getElementById("nbCol");
	var batSalle=document.getElementById("batSalle");
	var dptSalle=document.getElementById("dptSalle");
	var etageSalle=document.getElementById("etageSalle");
	
    if (!nomSalle) return 0; // Sécurité

	var ok=0;
	
	// ################ Test nom ################
	var tooltipStyle=getTooltip(nomSalle).style;
	if(nomSalle.value.length<2)
	{
		nomSalle.className="incorrect";
		tooltipStyle.display='inline-block';
	}
	else
	{
		nomSalle.className="correct";
		tooltipStyle.display='none';
		ok++;
	}

	// ################ Test nbRang ################
	tooltipStyle=getTooltip(rangSalle).style;
	if(parseInt(rangSalle.value)>1 && parseInt(rangSalle.value)<30)
	{
		rangSalle.className="correct";
		tooltipStyle.display='none';
		ok++;
	}
	else
	{
		rangSalle.className="incorrect";
		tooltipStyle.display='inline-block';
	}

	// ################ Test nbColonne ################
	tooltipStyle=getTooltip(colSalle).style;
	if(parseInt(colSalle.value)>1 && parseInt(colSalle.value)<30)
	{
		colSalle.className="correct";
		tooltipStyle.display='none';
		ok++;
	}
	else
	{
		colSalle.className="incorrect";
		tooltipStyle.display='inline-block';
	}

	// ################ Test etage ################
	tooltipStyle=getTooltip(etageSalle).style;
	if(etageSalle.value=='A')
	{
		etageSalle.className="incorrect";
		tooltipStyle.display='inline-block';
	}
	else
	{
		etageSalle.className="correct";
		tooltipStyle.display='none';
		ok++;
	}

	// ################ Test batiment ################
	tooltipStyle=getTooltip(batSalle).style;
	if(batSalle.value=='A')
	{
		batSalle.className="incorrect";
		tooltipStyle.display='inline-block';
	}
	else
	{
		batSalle.className="correct";
		tooltipStyle.display='none';
		ok++;
	}

	// ################ Test departement ################
	// Test department only if it exists (stage 1 logic)
    if(dptSalle) {
        tooltipStyle=getTooltip(dptSalle).style;
        if(dptSalle.value=='A' && batSalle.value=='3')
        {
            dptSalle.className="incorrect";
            tooltipStyle.display='inline-block';
        }
        else
        {
            dptSalle.className="correct";
            tooltipStyle.display='none';
            ok++;
        }
    } else {
        ok++;
    }
	
	return ok;
	
}


// ##### Affichage boutons #####

function affBtn()
{
    var stage = currentStageInput.value;

	if(stage=='stage1')
	{
		btnbef.style.display='none';
		btnnext.style.display='';
		btnsave.style.display='none';
	}
	else if(stage=='stage4')
	{
		btnbef.style.display='';
		btnnext.style.display='none';
		btnsave.style.display='';
	}
	else
	{
		btnbef.style.display='';
		btnnext.style.display='';
		btnsave.style.display='none';
	}
}

// #### Gestion AJAX et Scripts ####

function executeScripts(container) {
    var scripts = container.querySelectorAll("script");
    scripts.forEach(function(oldScript) {
        var newScript = document.createElement("script");
        Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
        newScript.appendChild(document.createTextNode(oldScript.innerHTML));
        oldScript.parentNode.replaceChild(newScript, oldScript);
    });
}

function loadStage(stageName, etapeNum) {
    var url = 'index.php?action=crea_salle&etape=' + etapeNum + '&ajax=1';

    fetch(url)
        .then(response => response.text())
        .then(html => {
            stageContainer.innerHTML = html;
            currentStageInput.value = stageName;
            executeScripts(stageContainer);
            affBtn();
        })
        .catch(err => {
            console.error('Erreur chargement étape:', err);
            stageContainer.innerHTML = "<p>Erreur lors du chargement de l'étape.</p>";
        });
}

// #### Gestion Navigation ####


// Bouton precedent
btnbef.addEventListener('click', function(e) {
    var stage = currentStageInput.value;

	switch(stage)
	{
		case "stage2": 	loadStage("stage1", 1);
						break;
						
		case "stage3":	loadStage("stage2", 2);
						break;
						 
		case "stage4":	loadStage("stage3", 3);
						break;
						
		default: 		break;
	}
}, false);

// Bouton suivant
btnnext.addEventListener('click', function(e) {
    var stage = currentStageInput.value;
	
	switch(stage)
	{
		case "stage1":	if(parseInt(checkChamp())==6)
						{
							var form = document.querySelector('form');
							var formData = new FormData(form);
							
							fetch(form.getAttribute('action'), {
								method: 'POST',
								body: formData
							})
							.then(response => {
								if (response.ok) {
									loadStage("stage2", 2);
								} else {
									console.error('Erreur validation stage 1');
								}
							})
							.catch(err => console.error('Erreur POST stage 1:', err));
						}
						break;
						
		case "stage2": 	var form = document.querySelector('form');
						var formData = new FormData(form);
						
						fetch(form.getAttribute('action'), {
							method: 'POST',
							body: formData
						})
						.then(response => {
							if (response.ok) {
								loadStage("stage3", 3);
							} else {
								console.error('Erreur validation stage 2');
							}
						})
						.catch(err => console.error('Erreur POST stage 2:', err));
						break;
						
		case "stage3": 	var donnee = buildDonnee();
						var form = document.querySelector('form');
						document.getElementById('donnee').value = donnee;
						var formData = new FormData(form);
						
						fetch(form.getAttribute('action'), {
							method: 'POST',
							body: formData
						})
						.then(response => {
							if (response.ok) {
								loadStage("stage4", 4);
							} else {
								console.error('Erreur validation stage 3');
							}
						})
						.catch(err => console.error('Erreur POST stage 3:', err));
						break;
						
		default: 		break;
	}
}, false);

// Bouton enregistrer
btnsave.addEventListener('click', function(e) {
	var form=document.getElementById('formSave');
    if(form) form.submit();
}, false);

// Initialisation etat boutons
affBtn();
