<?php 


// no direct access
defined('_JEXEC') or die('Restricted access');



$id=JRequest::getInt('id');
$uri=JRequest::getString('url');

//ajout le 18/02/2016 pour améliorer la sécurité
$uri=urldecode($uri);

if ($id && $uri)
{

}
else
{
	exit();
}

//BASE
$base=	JURI::base();
$base = str_replace('plugins/cck_field/avelook_btn_files_ressource/assets/scripts/','',$base);

//JOOMLA API + HELPERS
 				
require_once( JPATH_SITE.'/avelook/helpers/ressources.php');
require_once( JPATH_SITE.'/avelook/helpers/cck.php');
require_once( JPATH_SITE.'/libraries/mpdf/mpdf.php');


jimport('joomla.filesystem.path');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport( 'joomla.filesystem.archive' );

		
			
// GET TITLE OF FIRST RESSOURCE
/************************************************************************************************/

		$title=HelperRessources::getRessourceTitle($id);
			

// CREATE TEMP FOLDER
/************************************************************************************************/
	
		$temp = JPATH_BASE . "/plugins/cck_field/avelook_btn_files_ressource/tmp";
		$root=$temp ."/".$id.'/';
	
			// var_dump($root);
		if ( !JFolder::create($root) ) 
		{
			//Throw error message and stop script
			return false;
		}		
		

		
					

// FILES 
/************************************************************************************************/
		
		//generate pdf ressource
		

		//preparer le titre
		$title=string2url($title);
		
		
			$destination=$root.$title.'-fiche.pdf';
		
			
			generatePdf($id,$destination,$uri);
		
			
		//associate files
			
			//compte rendu
			$result=HelperRessources::getDefaultRessource(array(),array('association_fiches','files','compte_rendu'),$id);
			
			if ($result->compte_rendu)
			{
				$cr=$result->compte_rendu;
				$extension=JFile::getExt($cr);
				
				if ( !JFile::copy(JPATH_BASE.'/'.$cr, $root.'compte-rendu.'.$extension,'') ) {
				//Throw error message and stop script
				return false;
				}
			
			}
			
			//pièces jointes
			if ($result->files || $result->association_fiches)
			{
			
									
				// pièces jointes de la ressource en question
					$files=$result->files;
					$files=HelperCCK::parseFieldX('ave_ressource_file_uploaded','ave_ressource_file_uploaded_fieldx',$files);
					
					foreach ($files as $chem)
					{
						// foreach ($chem as $ch)
						// {
							$filename=JFile::getName($chem);
							
							if ( !JFile::copy(JPATH_BASE.'/'.$chem, $root.$filename,'') ) {
							//Throw error message and stop script
							return false;
							}
						// }
						
					}
				
				
					
				// pièces jointes des ressources associées
					$associated_files=$result->association_fiches;
					
					if($associated_files)
					{
						
						//folder
						$associations=$root.'ressources-associees/';
						if ( !JFolder::create($associations) ) 
						{
							//Throw error message and stop script
							return false;
						}
							
							
						$af=explode(',',$associated_files);
						
						
						
						foreach($af as $row)
						{
							$result=HelperRessources::getDefaultRessource(array('id','title'),array('files'),$row);
							$title_associated=HelperRessources::getRessourceTitle($row);
							$res=$result->files;
							$res=HelperCCK::parseFieldX('ave_ressource_file_uploaded','ave_ressource_file_uploaded_fieldx',$res);
							
								//create folder
									$fn=JFile::makeSafe($result->title);
									$resfolder=$associations.$fn.'/';
									
									if ( !JFolder::create($resfolder) ) 
									{
									//Throw error message and stop script
									return false;
									}
								
								
								//generate pdf
									$destination=$resfolder.$title_associated.'.pdf';
									generatePdf($row->id,$destination);
								
								
								
								
								foreach ($res as $fs)
								{
										$filename=JFile::getName($fs);
									
												if ( !JFile::copy(JPATH_BASE.'/'.$fs, $resfolder.$filename) ) 
												{
												//Throw error message and stop script
												return false;
												}
								}
							
							
						}
					
					}
			
					
					
					// Generate Zip file
				
							
					$destination=$root.$title.'.zip';
					Zip($root,$destination);
				
				
			}//end if
				
		
			
	
	

	

// OUTPUT
/************************************************************************************************/
	

			
		$file_name = $destination; //determiné plus haut
		
		// make sure it's a file before doing anything!
		if(is_file($file_name)) {

			/*
				Do any processing you'd like here:
				1.  Increment a counter
				2.  Do something with the DB
				3.  Check user permissions
				4.  Anything you want!
			*/

			// required for IE
			if(ini_get('zlib.output_compression')) { ini_set('zlib.output_compression', 'Off');	}

			// get the file mime type using the file extension
			switch(strtolower(substr(strrchr($file_name, '.'), 1))) {
				case 'pdf': $mime = 'application/pdf'; break;
				case 'zip': $mime = 'application/zip'; break;
				case 'jpeg':
				case 'jpg': $mime = 'image/jpg'; break;
				default: $mime = 'application/force-download';
			}
			
			header('Pragma: public'); 	// required
			header('Expires: 0');		// no cache
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Last-Modified: '.gmdate ('D, d M Y H:i:s', filemtime ($file_name)).' GMT');
			header('Cache-Control: private',false);
			header('Content-Type: '.$mime);
			header('Content-Disposition: attachment; filename="'.basename($file_name).'"');
			header('Content-Transfer-Encoding: binary');
			header('Content-Length: '.filesize($file_name));	// provide file size
			header('Connection: close');
			readfile($file_name);		// push it out
			unlink($file_name);
			
			//suppression du dossier temporaire
			// echo $root;
			JFolder::delete($root);
			
			
			exit();

		}
	
	
	
	
	
	
	

// FUNCTIONS
/************************************************************************************************/
	
function string2url($chaine) 
{ 
 $chaine = trim($chaine); 
 $chaine = strtr($chaine, 
"ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ", 
"aaaaaaaaaaaaooooooooooooeeeeeeeecciiiiiiiiuuuuuuuuynn"); 
 $chaine = strtr($chaine,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"); 
 $chaine = preg_replace('#([^.a-z0-9]+)#i', '-', $chaine); 
        $chaine = preg_replace('#-{2,}#','-',$chaine); 
        $chaine = preg_replace('#-$#','',$chaine); 
        $chaine = preg_replace('#^-#','',$chaine); 
 return $chaine; 
}




	
function generatePdf($id,$destination,$uri)
{


	
	$colcontent=array
	(
			'id',
			'catid',
	);
	
	$colress=array
	(
			'duree',
			'type',
			'vignette',
			'nbre_seances',
			'niveau',
			'pre_requis',
			'public',
			'competences_travaillees',
			'resume',
			'deroule',
			'association_fiches',
			'ressource_id',
			'files'
	);
			
	$result=HelperRessources::getDefaultRessource($colcontent,$colress,$id);		
	$title=HelperRessources::getRessourceTitle($result->ressource_id);
	
	//tags
		
		$tags			=	new JHelperTags;
		$taglist=$tags->getItemTags( 'com_content.article',$result->id);
		
	//prepare data 
		$data=array(
			'title'=>$title,
			'duree'=>$result->duree,
			'nbre_seances'=>$result->nbre_seances,
			'niveau'=>$result->niveau,
			'vignette'=>$result->vignette,
			'pre_requis'=>$result->pre_requis,
			'type'=>$result->type,
			'public'=>$result->public,
			'competences_travaillees'=>$result->competences_travaillees,
			'resume'=>$result->resume,
			'deroule'=>$result->deroule,
			'taglist'=>$taglist
		);
		
	
		$mpdf=new mPDF();
		$link=$uri.'?print=pdf&tmpl=pdf';
		// $link=JRoute::_(JURI::base().'index.php?option=com_content&view=article&id='.$result->id.'&Itemid=101&print=pdf&tmpl=pdf');
		$html=file_get_contents('http://'.$link);
	
		// $html=str_replace('/vdc/images/',JURI::base().'images/',$html);
		
		// var_dump($link);
		// var_dump($html);
		
		// exit();
		// $mpdf->CSSselectMedia='mpdf';
		$mpdf->WriteHTML($html);
						
	//create and copy pdf	
		$mpdf->Output($destination,'F');
		

}	
	
			
function getHtmlforMpdf($data)
	{
			
			
		$app = JFactory::getApplication('site');
	
		$doc = JFactory::getDocument();
		$path   = JURI::base().'templates/'.$app->getTemplate().'/';	

			
// var_dump($path);
			//place html in template		
			
			switch ($data['type'])
			{
				case 1:{
				$cattext=JTEXT::_('COM_CCK_RESSOURCE_TYPE_PARCOURS_PEDA');
				$color='bleu';}
				break;

				case 2:{
				$cattext=JTEXT::_('COM_CCK_RESSOURCE_TYPE_FICHE_ACTIVITE');
				$color='orange';}
				break;
				
				case 3:{
				$cattext=JTEXT::_('COM_CCK_RESSOURCE_TYPE_OUTIL');
				$color='vert';}
				break;
				
			}
			switch ($data['niveau'])
			{
				case 1:{
				$niveau_icon='<img src="'.$path.'images/debutant_'.$color.'.jpg">';
				$niveau_label=JTEXT::_('COM_CCK_RESSOURCE_NIVEAU_NOVICE');}
				break;

				case 2:{
				$niveau_icon='<img src="'.$path.'images/intermediaire_'.$color.'.jpg">';
				$niveau_label=JTEXT::_('COM_CCK_RESSOURCE_NIVEAU_INTERMEDIAIRE');}
				break;
				
				case 3:{
				$niveau_icon='<img src="'.$path.'images/expert_'.$color.'.jpg">';
				$niveau_label=JTEXT::_('COM_CCK_RESSOURCE_NIVEAU_EXPERT');}
				break;
				
			}
			switch ($data['public'])
			{
				case 1:{
				$public_icon='<img src="'.$path.'images/enfant_'.$color.'.jpg">';
				$public_label=JTEXT::_('COM_CCK_RESSOURCE_PUBLIC_ENFANT');}
				break;

				case 2:{
				$public_icon='<img src="'.$path.'images/ado_'.$color.'.jpg">';
				$public_label=JTEXT::_('COM_CCK_RESSOURCE_PUBLIC_ADO');}
				break;	
				case 3:{
				$public_icon='<img src="'.$path.'images/adulte_'.$color.'.jpg">';
				$public_label=JTEXT::_('COM_CCK_RESSOURCE_PUBLIC_ADULTE');}
				break;
				
				case 4:{
				$public_icon='<img src="'.$path.'images/public_'.$color.'.jpg">';
				$public_label=JTEXT::_('COM_CCK_RESSOURCE_PUBLIC_TOUS');}
				break;	
				case 5:{
				$public_icon='<img src="'.$path.'images/public_'.$color.'.jpg">';
				$public_label=JTEXT::_('COM_CCK_RESSOURCE_PUBLIC_VDC');}
				break;
				
			}
		
		
	
			
			
			$html='<link rel="stylesheet" href="'.$path.'css/bootstrap.min.css" type="text/css" media="print">
				<link rel="stylesheet" href="'.$path.'css/mypdf.css" type="text/css" media="print" />
				<link href="http://fonts.googleapis.com/css?family=Cabin:400,500,600" rel="stylesheet" type="text/css">
		<div class="ressource_detail '.$color.' row">
				<div class="ressource_detail_header ">
					<div class="col-md-9">
						<div class="bloc1">
							'.$cattext.'
							'.$data['title'].'
						</div>								
						<div class="bloc2">
							<div class="partie1">
								'.$niveau_icon.'
								<span>'.$niveau_label.'</span>
							</div>								
							<div class="partie2">
								'.$public_icon.'
								<span>'.$public_label.'</span>
							</div>						
						</div>						
					</div>
					<div class="col-md-3">
						<img src="'.JURI::base(true).'/'.$data['vignette'].'">
					</div>
				</div>
				<div class="ressource_contenu_box">
					<h3>'.JTEXT::_('COM_CCK_RESSOURCE_PREREQUIS').'</h3>	
					<p>'.$data['pre_requis'].'</p>
					<h3>'.JTEXT::_('COM_CCK_RESSOURCE_RESUME').'</h3>
					<p>'.$data['resume'].'</p>
					<h3>'.JTEXT::_('COM_CCK_RESSOURCE_COMPETENCES_TRAVAILLEES').'</h3>
					<p>'.$data['competences_travaillees'].'</p>
				</div>
				<div class="ressource_contenu">
					<h2>'.JTEXT::_('COM_CCK_RESSOURCE_DEROULE').'</h2>
					'.$data['deroule'].'
				</div>
			</div>';



			
		
		//get template	
					

 // var_dump($path.'css/bootstrap.min.css');
						// $doc = new JDocumentHtml();
						// $options['directory'] = JPATH_BASE.'/templates';
						// $options["template"] = $app->getTemplate();//get the template name
						// $options["file"] = "pdf.php";//usually
						// $document = JFactory::getDocument();
						// $document->setBuffer($html, 'component', $name);
						// $html= $doc->render(false, $options);
						// var_dump($html);
						// $app->close();
			
			
			
			
			
			
			
			
			
			
			
			return $html;

	}

function Zip($source, $destination)
	{

		if (!extension_loaded('zip') || !file_exists($source)) {
		return false;
		}

		$zip = new ZipArchive();
		if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
		return false;
		}

		$source = str_replace('\\', '/', realpath($source));

		if (is_dir($source) === true)
		{
		$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

		foreach ($files as $file)
		{
		$file = str_replace('\\', '/', $file);

		// Ignore "." and ".." folders
		if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) )
		continue;

		if (is_dir($file) === true)
		{
		$zip->addEmptyDir(str_replace($source . '/', '', $file));
		}
		else if (is_file($file) === true)
		{

		$str1 = str_replace($source . '/', '', '/'.$file);
		$zip->addFromString($str1, file_get_contents($file));

		}
		}
		}
		else if (is_file($source) === true)
		{
		$zip->addFromString(basename($source), file_get_contents($source));
		}

		return $zip->close();
	}	


	
	
	
	
	
	
	
	
	
	
