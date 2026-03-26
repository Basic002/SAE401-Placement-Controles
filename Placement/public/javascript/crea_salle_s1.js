var chxBat=document.getElementById('batSalle');
var chxDpt=document.getElementById('dptSalle');

chxBat.addEventListener('change', function(e) {
	if(chxBat.value=='3')
	{
		chxDpt.style.display='';
	}
	else
	{
		chxDpt.style.display='none';
	}
}, false)

	if(chxBat.value=='3')
	{
		chxDpt.style.display='';
	}
	else
	{
		chxDpt.style.display='none';
	}