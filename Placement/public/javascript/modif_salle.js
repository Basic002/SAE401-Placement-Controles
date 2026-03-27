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
	var id_salle=document.getElementById("id_salle");
	
    if (!id_salle) return 0; // Sécurité

	var ok=0;
	
	// ################ Test salle ################
	if(id_salle.value=='')
	{
		id_salle.className="incorrect";
		ok=0;
	}
	else
	{
		id_salle.className="correct";
		ok=1;
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
	else if(stage=='stage6')
	{
		btnbef.style.display='';
		btnnext.style.display='none';
		btnsave.style.display='';
	}
	else if(stage=='stage3')
	{
		btnbef.style.display='';
		btnnext.style.display='';
		btnsave.style.display='none';
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
    var url = 'index.php?action=modif_salle&etape=' + etapeNum + '&ajax=1';

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
						
		case "stage5":	loadStage("stage4", 4);
						break;
						
		case "stage6":	loadStage("stage5", 5);
						break;
						
		default: 		break;
	}
}, false);

// Bouton suivant
btnnext.addEventListener('click', function(e) {
    var stage = currentStageInput.value;
	
	switch(stage)
	{
		case "stage1":	if(parseInt(checkChamp())==1)
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

		case "stage3": 	var form = document.querySelector('form');
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
						
		case "stage4": 	var donnee = buildDonnee();
						var form = document.querySelector('form');
						document.getElementById('donnee').value = donnee;
						var formData = new FormData(form);
						
						fetch(form.getAttribute('action'), {
							method: 'POST',
							body: formData
						})
						.then(response => {
							if (response.ok) {
								loadStage("stage5", 5);
							} else {
								console.error('Erreur validation stage 4');
							}
						})
						.catch(err => console.error('Erreur POST stage 4:', err));
						break;

		case "stage5": 	var form = document.querySelector('form');
						var formData = new FormData(form);
						
						fetch(form.getAttribute('action'), {
							method: 'POST',
							body: formData
						})
						.then(response => {
							if (response.ok) {
								loadStage("stage6", 6);
							} else {
								console.error('Erreur validation stage 5');
							}
						})
						.catch(err => console.error('Erreur POST stage 5:', err));
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
