<?php
/**
 * Catalan language file
 * @author Joseo Cañellas<jcanell4@ioc.cat>
 */
require_once (DOKU_INC . 'lib/plugins/ajaxcommand/defkeys/GlobalKeys.php');

const PAG_DEFAULT = "createDefaultText";
const PAG_SAVED = "saved";
const PAG_DELETED = "deleted";

$lang['yes'] = 'Sí';
$lang['no'] = 'No';
$lang['createDefaultText'] = 'Pàgina creada per defecte';
$lang['conflictsSaving'] = "Hi ha conflictes amb altres versions. No s'han pogut guardar els canvis";
$lang['saved'] = "Els canvis s'han guardat correctament";
$lang['section_saved'] = "S'han guardat els canvis de la secció %s";
$lang['deleted'] = "La pàgina %s ha estat eliminada";
$lang['reverted'] = "La reversió s'ha completat correctament";
$lang['pageNotFound'] = "La pàgina %s no s'ha trobat";
$lang['pageExists'] = "La pàgina %s ja existeix";
$lang['metaEditForm'] = "Camps Edició";
$lang['project_loaded'] = "El projecte s'ha carregat correctament";
$lang['project_view'] = "Es mostren les dades actuals del projecte.";
$lang['project_edited'] = "El projecte està en mode edició. Prem el botó [Desa] per desar les dades.";
$lang['project_reverted'] = "El projecte s'ha revertit amb éxit a la versió de la revisió indicada";
$lang['project_canceled'] = "S'ha cancel·lat l'edició del projecte ";
$lang['project_revision'] = "Aquesta és una revisió antiga del projecte";
$lang['form_compare'] = "Es mostren les dades comparades del projecte i la revisió seleccionada: ";
$lang['form_compare_rev'] = "Es mostren les dades comparades de les dos revisions seleccionades: ";

$lang['auth_CreatePage'] = "No teniu permís per a Crear la pàgina %s";
$lang['auth_EditPage'] = "No teniu permís per a Editar la pàgina %s";
$lang['auth_ViewPage'] = "No teniu permís per a Veure la pàgina %s";
$lang['auth_WritePage'] = "No teniu permís per a Escriure la pàgina %s";
$lang['auth_DeletePage'] = "No teniu permís per a Eliminar la pàgina %s";
$lang['auth_DeleteResource'] = "No teniu permís per a Eliminar el recurs %s";
$lang['auth_UploadMedia'] = "No teniu permís per a pujar fitxers";
$lang['auth_TokenNotVerified'] = "Token no verificat";
$lang['auth_UserNotAuthenticated'] = "Usuari no autenticat";
$lang['auth_CommadNotAllowed'] = "Comanda no permesa per a %s";
$lang['DraftNotFoundException'] = "No s'ha trobat l'esborrany del document %s";
$lang['UnexpectedLockCode'] = "Codi de bloqueig '%s' desconegut. No hi ha cap acció associada.";
$lang['UnknownUser'] = "No s'ha trobat cap usuari amb el identificador: %s";

$lang['lockedByDialog'] = "El document està bloquejat per %s. Vols obrir-lo en mode de només lectura o demanar el bloqueig";
$lang['lockedByAlert'] = "El document està bloquejat per altre usuari";
$lang['lockedByTitle'] = "Document bloquejat";
$lang['BtnReadOnly'] = "Només lectura";
$lang['BtnRequireLock'] = "Demanar el bloqueig";
$lang['alreadyLocked'] = "Teniu obert aquest document en una altra màquina o en una altre sessió. Per poder-lo editar tanqueu-lo prèviement.";
$lang['documentRequired'] = "%d usuari(s) requereix(en) el document %s. Si no el necessites, si us plau, tanca la edició.";
$lang['documentUnrequired'] = "Actualment, ja ningú necessita el document %s.";
$lang['documentUnlocked'] = "El document %s ha sigut alliberat. Cancela la edició i torna a editar per accedir";
$lang['structuredDocumentUnlocked'] = "El document ha estat alliberat. Cancel•la l'edició, o bé, obre un altre fragment per accedir a l'edició";

$lang['requiring_message'] = "El document %s està bloquejat per l'usuari %s. S'estima que quedarà alliberat a les %s";
$lang['requiring_dialog_title'] = "ALERTA. Document bloquejat!";
$lang['requiring_dialog_message'] = "El document %s està bloquejat per l'usuari %s. S'estima que quedarà alliberat a les %s\n"
        . "Vols que t'avisem quan es desbloquegi aquest document?\n"
        . "Això enviarà una notificació a %s indicant que t'interessa editar el document %s i que l'alliberi l'abans possible.\n"
        . "Activem la notificació?";
$lang['require_message'] = "El projecte %s està bloquejat per l'usuari %s.<br>S'estima que quedarà alliberat a les %s";
$lang['require_dialog_title'] = "ALERTA. Projecte bloquejat!";
$lang['require_dialog_message'] = "El projecte %s està bloquejat per l'usuari %s.<br>S'estima que quedarà alliberat a les %s<br>"
        . "Vols que t'avisem quan es desbloquegi aquest projecte?<br>"
        . "Això enviarà una notificació a %s indicant que t'interessa editar el projecte %s i que l'alliberi l'abans possible.<br>"
        . "Activem la notificació?";

$lang['tab_shortcuts'] = "Dreceres";
$lang['changes_type_filter'] = "Filtre dels canvis recents";
$lang['changes_navigation'] = "Paginació dels canvis recents";
$lang['recent_controls'] = "Controls dels canvis recents";
$lang['recent_list_loaded'] = "Llista dels canvis recents carregada";
$lang['recent_list'] = "canvis recents";

// Avisos del sistema
$lang['system_warning_default_title'] = 'Avís del sistema';

// Plantilles
$lang[GlobalKeys::TEMPLATE_SHORTCUTS_NS] = "Nou document de dreceres.\n\n"
    . "Per afegir dreceres a documents o espais de noms, afegir enllaços al document o a l'espai de noms, seguint la sintaxi wiki. Per exemple:\n\n"
    . "[[wiki:user:%nom_d_usuari%|El meu espai]]\n\n"
    . "[[wiki:navigation|Drecera a la documentació de la wiki]]";
//Excepciones del proyecto 'defaultProject'
$lang['pageExist'] = 'The page %s already exists';
$lang['commandAuthorizationNotFound'] = 'Command authorization not found';
$lang['cantCreatePageInProject'] = 'No pots crear una pàgina en aquest projecte';
$lang['ClassNotFound'] = "No s'ha trobat la classe %s";

//Excepciones del proyecto 'documentation'
$lang['projectException']['projectAlreadyGenerated'] = 'El projecte %s ja està generat. No es pot tornar a generar.';
$lang['projectException']['projectExist'] = 'The project %s already exist';
$lang['projectException']['projectNotExist'] = 'The project %s not already exist';
$lang['projectException']['unknown'] = 'Unknown project exception';
$lang['projectException']['userNotAuthorized'] = 'Vosté no té permís a %s';
$lang['projectException']['authorNotVerified'] = 'Vosté no té permís a %s';
$lang['projectException']['responsableNotVerified'] = 'Vosté no té permís a %s';
$lang['projectException']['insufficientPermissionToEditProject'] = 'Vosté no té permís per editar el projecte %s';
$lang['projectException']['insufficientPermissionToCreateProject'] = 'Vosté no té permís per crear el projecte %s aquí';
$lang['projectException']['insufficientPermissionToDeleteProject'] = 'Vosté no té permís per eliminar el projecte %s';
$lang['projectException']['insufficientPermissionToGenerateProject'] = 'Vosté no té permís per generar el projecte %s';

$lang['title_message_notification'] = 'Missatge de %s';
$lang['title_message_notification_with_id'] = 'Missatge de %s (document %s)';
$lang['doc_message'] = 'Missatge referent a: [[%s|%s]].';
$lang['doc_message_with_rev'] = 'Missatge referent a: [[%s|%s (%s)]].';
$lang['mail_message'] = "S'ha enviat una notificació a la [[%s|wiki]] referent a //%s//, amb el missatge següent: ";
$lang['message_notification_receivers'] = '**Destinataris**: %s';
$lang['notifation_send_success'] = 'Notificación enviada amb èxit. Destinataris: %s';


$lang['notification_form_title'] = 'Enviar notificacions';
$lang['notification_form_to'] = 'Destinatari';
$lang['notification_form_check_add_id'] = 'Afegir enllaç al document %s';
$lang['notification_form_check_add_email'] = 'Enviar correu';
$lang['notification_form_button_send'] = 'Enviar';
$lang['notification_form_message'] = 'Missatge';
$lang['notificaction_email_subject'] = 'Nova notificació - %s';

$lang['projectLabelForm']['responsable'] = 'responsable del projecte';
$lang['projectLabelForm']['autor'] = 'autor del projecte';
$lang['projectLabelForm']['titol'] = 'titol del projecte';
$lang['projectLabelForm']['plantilla'] = 'plantilla per defecte';
$lang['projectLabelForm']['descripció'] = 'descripció del projecte';

$lang['projectGroup']['main'] = 'Grup principal del projecte';
$lang['projectGroup']['admin'] = 'Dades de l\'administració del projecte';

$lang['cancel_editing_with_changes'] = 'S\'han produït canvis al document. Vols tancar-lo?';
$lang['save_or_discard_dialog_title'] = 'Desar abans de cancel·lar';
$lang['save_or_discard_dialog_message'] = 'Vols desar els canvis abans de cancel·lar?';
$lang['save_or_discard_dialog_dont_save'] = 'No desar';
$lang['save_or_discard_dialog_save'] = 'Desar';
$lang['search'] = 'Cercar';

$lang['projects']['cancel_editing_with_changes'] = "S'han produït canvis a les dades del projecte. Vols tancar el formulari?";
