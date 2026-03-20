// ############ Gestion boutons Precedent/Suivant ############
var stageContainer = document.getElementById('stage-content');
var currentStageInput = document.getElementById('currentStageName');

var btnbef=document.getElementById('btnbef');
var btnnext=document.getElementById('btnnext');
var btnsave=document.getElementById('btnsave');

var PATH_PREFIX = "views/salle/"; // Chemin relatif depuis la racine (index.php)

function recupVar()
{
    // Fonctionnalité intégrée directement lors du changement d'étape si nécessaire
}


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

function loadStage(url, stageName) {
    var fetchUrl = url;
    // Si l'URL ne commence pas par le prefixe, on l'ajoute
    if (fetchUrl.indexOf(PATH_PREFIX) === -1) {
        fetchUrl = PATH_PREFIX + fetchUrl;
    }

    fetch(fetchUrl)
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

// Interception des liens internes (ex: ajout lignes/colonnes étape 2)
stageContainer.addEventListener('click', function(e) {
    var target = e.target.closest('a');
    if (target) {
        var href = target.getAttribute('href');
        // Si le lien pointe vers une étape php (gestion dynamique interne)
        if (href && href.indexOf('cs_stage') !== -1) {
            e.preventDefault();
            // On détermine le stageName basé sur le fichier php cible
            var newStageName = currentStageInput.value;
            if (href.indexOf('cs_stage1.php') !== -1) newStageName = 'stage1';
            else if (href.indexOf('cs_stage2.php') !== -1) newStageName = 'stage2';
            else if (href.indexOf('cs_stage3.php') !== -1) newStageName = 'stage3';
            else if (href.indexOf('cs_stage4.php') !== -1) newStageName = 'stage4';
            
            loadStage(href, newStageName);
        }
    }
});


// #### Gestion Navigation ####


// Bouton precedent
btnbef.addEventListener('click', function(e) {
    var stage = currentStageInput.value;

	switch(stage)
	{
		case "stage2": 	loadStage("cs_stage1.php", "stage1");
						break;
						
		case "stage3":	loadStage("cs_stage2.php", "stage2");
						break;
						 
		case "stage4":	loadStage("cs_stage3.php", "stage3");
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
                            var nomSalle=document.getElementById("nomSalle");
                            var rangSalle=document.getElementById("nbRang");
                            var colSalle=document.getElementById("nbCol");
                            var batSalle=document.getElementById("batSalle");
                            var dptSalle=document.getElementById("dptSalle");
                            var etageSalle=document.getElementById("etageSalle");

                            var url = "cs_stage2.php?var1="+encodeURIComponent(nomSalle.value)+
                                      "&var2="+encodeURIComponent(rangSalle.value)+
                                      "&var3="+encodeURIComponent(colSalle.value)+
                                      "&var4="+encodeURIComponent(batSalle.value)+
                                      "&var5="+encodeURIComponent(dptSalle.value)+
                                      "&var6="+encodeURIComponent(etageSalle.value);
							
                            loadStage(url, "stage2");
						}
						break;
						
		case "stage2": 	loadStage("cs_stage3.php", "stage3");
						break;
						
		case "stage3": 	loadStage("cs_stage4.php", "stage4");
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
