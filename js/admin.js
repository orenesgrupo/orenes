jQuery(function($) {
	$(document).on('click', 'a.thickbox[href*="github.com"], a.open-plugin-details-modal[href*="github.com"]', function(e){
		try { e.preventDefault(); e.stopImmediatePropagation(); } catch(e){}
		var $a = $(this);
		clean_link($a);
		window.open($a.attr('href'), '_blank');
	});
	$(document).ready(function(){
		sweep_link();
	});
	$(document).on('TB_onLoad', function(){
		sweep_link();
	});
	var observer = new MutationObserver(function(mutations){
		mutations.forEach(function(m){
			m.addedNodes && Array.prototype.forEach.call(m.addedNodes, function(node){
				if (node.nodeType !== 1) return;
				var $node = $(node);
				// buscar enlaces dentro del nodo o el propio nodo
				$node.find('a.thickbox[href*="github.com"], a.open-plugin-details-modal[href*="github.com"]').each(function(){
					clean_link($(this));
				});
				if ($node.is('a.thickbox[href*="github.com"], a.open-plugin-details-modal[href*="github.com"]')) {
					clean_link($node);
				}
			});
		});
	});
	observer.observe(document.documentElement || document.body, { childList: true, subtree: true });
});

function clean_link(element) {
	if (!element || !element.length) return;
	var href = element.attr('href') || '';
	if (!href) return;
	href = href.split('?')[0]; // quitar query string
	element.removeAttr('class'); // eliminar atributo class
	element.attr({
		'href': href,
		'target': '_blank',
		'rel': 'noopener noreferrer'
	});
}

function sweep_link() {
	jQuery('a.thickbox[href*="github.com"], a.open-plugin-details-modal[href*="github.com"]').each(function(){
		clean_link(jQuery(this));
	});
}

function check_email(email) {
	var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
	return re.test(email);
}