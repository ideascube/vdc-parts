<?php
/**
 * @package     Avelook3.Template
 * @copyright   Copyright (C) 2010 - 2014 Avelook. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

// Template folders for easy management
$tplUrl  = JUri::root(false) . '/templates/' . $this->template;
$tplPath = JPATH_SITE . '/templates/' . $this->template;

/**
 * We will load our own jQuery & Bootstrap HTML classes for 2.5
 * These libraries doesn't work (jQuery & Bootstrap are loaded from template directly)
 * This is here to avoid JHtml errors and improve compatibility
 */
if (version_compare(JVERSION, '3.0', '<'))
{
	JHtml::addIncludePath($tplPath . '/includes/html');
}

// Defaults
$app                = JFactory::getApplication();
$doc                = JFactory::getDocument();
$lang               = JFactory::getLanguage();
$langTag            = $lang->getTag();
$user               = JFactory::getUser();
$this->language     = $doc->language;
$langParts          = explode('-', $this->language);
$htmlLang           = reset($langParts);
$option             = $app->input->getCmd('option', '');
$view               = $app->input->getCmd('view', '');
$layout             = $app->input->getCmd('layout', '');
$task               = $app->input->getCmd('task', '');
$itemid             = $app->input->getCmd('Itemid', '');
$login_button       = $this->params->get('userLogin');
$register_button    = $this->params->get('userRegistration');
$totop              = $this->params->get('toTop');
$frontpageshow      = $this->params->get('frontpageshow', 0);
$left               = $this->params->get('sidebarLeftWidth', '');
$right              = $this->params->get('sidebarRightWidth', '');
$adminfrontend      = $this->params->get('adminfrontend', '');
$Menu404page        = $this->params->get('404');

  $menu = $app->getMenu()->getActive();
  $pageclass = '';
 
  if (is_object($menu))
    $pageclass = $menu->params->get('pageclass_sfx');



// Set the generator metadata
$doc->setGenerator($this->params->get('setGeneratorTag', ''));




/**
 * ==================================================
 * Page communaut� col left
 * ==================================================
 */
 
// $left               = $this->params->get('sidebarLeftWidth', '');
if($menu)
{
	$_string = $menu->params->get('pageclass_sfx');
	$_request = "communaute";
	if(preg_match('#'.$_request.'#', $_string)){
	  $left="5";
	}
}




/**
 * ==================================================
 * Redirect en => fr
 * ==================================================
 */
 
 if (JRequest::getVar('lang')=='en-GB')
 {
	// $app = JFactory::getApplication();
	// $link = 'index.php';
	// $msg = 'This version is not available. Sorry';
	// $app->redirect($link, $msg, $msgType='message');
 }
 
 
/**
 * ==================================================
 * Geolocalisation BY IP => SESSION VAR
 * ==================================================
 */

//is the ip in session or not ?
//get coord with IP API .Com (not commercial use only)

	$session =& JFactory::getSession();
	
	if (!$session->get( 'geoip'))
	{
		$ip = $_SERVER['REMOTE_ADDR'];
        // EDIT from philippe.sabaty@gmail.com on 2017-11-22 - Start
		//$details = json_decode(file_get_contents("http://ip-api.com/json/{$ip}"));
        $providerUrl = "http://freegeoip.net/json/{$ip}";
		$timeout = 10; // using timeout for smoother behaviour when quotas are reached
        $providerData = file_get_contents(
            $providerUrl, false,
            stream_context_create( array('http'=>array('timeout' => $timeout )) )
        );
        $details = $providerData?json_decode($providerData):null;
        // copying some values expected from previous provider, for compatibility
        if($details){
            if(isset($details->latitude)){$details->lat = $details->latitude;}
            if(isset($details->longitude)){$details->lon = $details->longitude;}
            if(isset($details->country_name)){$details->country = $details->country_name;}

        }
        // EDIT from philippe.sabaty@gmail.com on 2017-11-22 - End
		$session->set( 'geoip',$details );
	}
	
/**
 * ==================================================
 * Set Itemid of 404 page
 * ==================================================
 */

if (JRequest::getVar('Itemid')==$Menu404page)
{
header('HTTP/1.0 404 Not Found');
}


/**
 * ==================================================
 * Frontpage check
 * ==================================================
 */
$isFrontpage = false;
$menu = JFactory::getApplication()->getMenu();

// Single language sites
if (!JLanguageMultilang::isEnabled())
{
	if ($menu->getActive() == $menu->getDefault())
	{
		$isFrontpage = true;
	}
}
elseif ($menu->getActive() == $menu->getDefault($langTag))

// Multilanguage sites
{
	$isFrontpage = true;
}

$frontpage = $isFrontpage ? 'frontpage' : '';

$fullWidth = ($task == "edit" || $layout == "form" ) ? 1 : 0;

// Width calculations
$span = '';
$grid = 12;

if ($this->countModules('left') && $this->countModules('right'))
{
	$span = ($grid - ( $left + $right ));
}
elseif ($this->countModules('left') && !$this->countModules('right'))
{
	$span = ($grid - $left);
}
elseif (!$this->countModules('left') && $this->countModules('right'))
{
	$span = ($grid - $right);
}
else
{
	$span = 99;
}



/**
 * ==================================================
 * CALL HELPERS
 * ==================================================
 *//**/

require_once(JPATH_SITE."/avelook/helpers/cck.php");
$CCKHelper =   new HelperCCK();


		
		
		
/**
 * ==================================================
 * Magnific Popup
 * Magnific Popup + fonction submit dans instance de popup
 * ==================================================
 *//**/
 
 
// Add Javascript
$document = JFactory::getDocument();
$document->addScriptDeclaration('


       
	   
	   
	   
//////CONTROL UPLOAD


jQuery(document).ready(function($) {
  jQuery(".upload_image").bind("change", function(e) {
		$val_size=Math.round((this.files[0].size/1024)/1024, 2);
		var ftype = this.files[0].type;
			switch (ftype) {
				case "image/png":
				case "image/gif":
				case "image/jpeg":
				case "image/jpg":
					jQuery(this).validationEngine("hide");
					break;
				default:
				{
					 jQuery(this).validationEngine("showPrompt", "Le fichier doit avoir comme extension jpg,jpeg,gif ou png", "load");
					$(this).wrap("<form>").closest("form").get(0).reset(); $(this).unwrap(); 

				}
    }	
		 if ($val_size>5) 
		 { 

			 // alert(this.files[0].size);
			 jQuery(this).validationEngine("showPrompt", "Le fichier doit etre <5Mo", "load");
			 $(this).wrap("<form>").closest("form").get(0).reset(); $(this).unwrap(); 
				// $(this).after($(this).clone(true)).remove();
				 
		 }		
	// console.log($(this).val());
  });

});

//HACK CONFLIT PLUGIN SELECTBOX BOOTSTRAP + SEBLOD CONDITIONNAL STATES ON SELECT !!!!/////////////////////////////////

	jQuery(document).ready(function($){

	 $("input[name=\"ave_evenement_recurrence\"]").change(function () {

	 if ($("#ave_evenement_recurrence0").is(":checked"))
	 {
	 console.log( $(this).parents("div").next());
	 $(this).parents("div").next().find("button").removeClass("disabled");
	 }
			  
	 });

	});





//MY MODAL SCRIPT !!!!/////////////////////////////////


jQuery(document).ready(function(){

	jQuery(".btn").click(function(){

		jQuery("#myModal").modal("show");

	});
	
	// jQuery(".evenement_color4 .btn_animer").parent("div").find(".btn_insc").hide();
	
	
	

});



//INITJS !!!!/////////////////////////////////



jQuery(document).ready(function() 
{
	initjs();
});



//MASONRY !!!!/////////////////////////////////

function masonryInit()
{ 
	jQuery("#cck1r .masonry").masonry({"itemSelector":".item","isAnimated":true,"isFitWidth":true}
);
}






//ROYALSLIDER !!!!/////////////////////////////////

	function royalSliderInit()
	{ jQuery(".royalSlider").royalSlider({
    fullscreen: {
      enabled: true,
      nativeFS: true
    },
    controlNavigation: "thumbnails",
    autoScaleSlider: false, 
	autoHeight: false,
    loop: true,
    imageScaleMode: "fit-if-smaller",
    navigateByClick: true,
    numImagesToPreload:1,
    arrowsNav:true,
    arrowsNavAutoHide: false,
    arrowsNavHideOnTouch: false,
    keyboardNavEnabled: true,
    fadeinLoadedSlide: true,
    globalCaption: true,
    globalCaptionInside: true,
    thumbs: {
      appendSpan: true,
      firstMargin: true,
      paddingBottom: 4,
	  orientation :	"horizontal"
    }
  });
  
  };
  
  
  

//FONCTION INITJS SE DECLENCHE APRES CHAQUE APPEL AJAX !!!!/////////////////////////////////
  
function initjs()
{
	  magnificpopup();
	  paginationajax();
	  jQuery("[data-toggle=\"tooltip\"]").tooltip({html : true });
	  
}



//MAGNIFIC POPUP !!!!/////////////////////////////////

function magnificpopup()
{


	// MAGNIFIC POPUP 
	//bug  https://github.com/dimsemenov/Magnific-Popup/issues/616


			//popup magnific popup ajax call with submit in ajax
			
			jQuery(".simple-ajax-popup-align-top").magnificPopup({
			
            type: "iframe",
              iframe: {
                 markup: "<div class=\"mfp-iframe-scaler\">"+
                            "<iframe class=\"mfp-iframe\" frameborder=\"0\" scrolling=\"yes\" allowtransparency=\"true\" ></iframe>"+
                           "<div class=\"mfp-close\"></div>"+
                          "</div>"
             },
			 mainClass: "mfp-fade"
			 
			 
			 
  
  
			 });
			 
			 
			 
			 
			 
};	




/*	type: "ajax",
				// alignTop: true,
				overflowY: "scroll",
				mainClass:"width-blank mfp-fade",
				removalDelay: 300,
				preloader:true,
				closeOnContentClick:false,
				closeBtnInside:true,loseOnContentClick:false,
				closeOnBgClick:false,
				closeBtnInside:true,
				tLoading: "<img src=\"'.JURI::base().'/media/cck/images/spinner.gif\"/>",
				callbacks : {
				
					// parseAjax: function(mfpResponse) {
					// }
					// ,
					
					// ajaxContentAdded:function()
					// {
					// console.log(this.content);
					// },
								
					// beforeOpen: function() 
					// {
						// var mp = jQuery.magnificPopup.instance;
						
						// console.log(mp.contentContainer);
					  
					// },
				
  
					open  : function()
					{
						var mp = jQuery.magnificPopup.instance,
						  t = jQuery(mp.currItem.el[0]);
						  ajaxSubmitinPopup(mp);
						// jQuery(form).validationEngine();
						// console.log(mp.st.el);
					}
					
  
  
					
						
				}*/



//PAGINATION AJAX !!!!/////////////////////////////////

function paginationajax()
{

		 
	//pagination ajax
		
				var element = jQuery("#seblod_form_load_more");
				if (element.length)
				{
					
					if(element.css("display") != "none")
					{
					var waypoint = new Waypoint({
					  element: document.getElementById("seblod_form_load_more"),
					  handler: function(direction) {
						jQuery("#seblod_form_load_more").trigger("click");
						
					  },
					  offset: "90%"
					});

					magnificpopup();
					}	 
					
				}
}






//AJAX SUBMIT IN POPUP !!!!/////////////////////////////////	
			 
			function ajaxSubmitinPopup(mp)
			{			
			
				jQuery(".submit_form_ajax").live("click", mp, function(e) 
			
				{
					
					 var configtype=jQuery("input[name=\"config[type]\"]").val();//bug seblod ????
					
					
					 if (jQuery(this).parents("#wrapper-inline").find("form").validationEngine("validate",task) === true) 
					 { 
					
							
							
							var url=jQuery(this).parents("form").attr("action");
							var datas=jQuery(this).parents("form").serialize();
							var datas=decodeURIComponent(datas);
							
							// var url="http://www.clients.avelook.fr/vdc/fr/profil-membre/mes-messages/message-prive/form/message_prive.html";
							var datas=datas+"&task=save&config[unique]=seblod_form_"+configtype;
						
							jQuery.ajax({
							type: "POST",
							url: url,
							context:this,
							data: datas  ,
							beforeSend: function() 
								{
								// console.log(jQuery(this).find("#ave_button_submit_ajax"));
								// console.log(mp);
								jQuery(this).replaceWith("<span class=\"loading\"><img src=\"'.JURI::base().'/media/cck/images/spinner.gif\"/></span>");
								
								},
							success: function(msg)
								{
									console.log(this);
									var output=jQuery(msg).find("#message-component").html();
									jQuery("#wrapper-inline .cck_page").html(output);
									
										

								}
							});
							return false;
					}
						
						
				});
				
				initjs();
			 
			};
			
			
			
//FONCTION HIDE BTN AVIS !!!!/////////////////////////////////	

function hidebtn(elemclass)
{	

// alert("okok");
		// jQuery(elemclass).find(".btn_avis").replaceWith("<p class=\"btn blanc_button\">'.JText::_('BTN_AVIS_DEJA_DONNE').'</p>");
	

}









');
