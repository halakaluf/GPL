function initBuyCards(){
	$$('.gpl .bandeira-container').each(function(span){
		span.observe('click', function(event){
			  $$('.gpl .bandeira-container').each(function(span){
					span.removeClassName('bandeira-container-selecionada');
					$('form-'+$(span).readAttribute('id')).writeAttribute("style", "display: none;");
opa = $('form-vi','numParcelas-1');
opa[1].writeAttribute("checked", "checked");
			  });
			  span.addClassName('bandeira-container-selecionada');
	//		  alert($(span).readAttribute('id'));
			  $('form-'+$(span).readAttribute('id')).writeAttribute("style", "display: block;");
			 $('idOperadora').writeAttribute("value", $(span).readAttribute('id'));

		});

	});
}
