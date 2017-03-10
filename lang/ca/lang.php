<?php
/**
 * Catalan language file
 *
 * @author Joseo Cañellas<jcanell4@ioc.cat>
 */
const PAG_DEFAULT = "createDefaultText";
const PAG_SAVED = "saved";
const PAG_DELETED = "deleted";

//Setup VIM: ex: et ts=2 enc=utf-8 :

$lang['createDefaultText'] = 'Pàgina creada per defecte';
$lang['conflictsSaving'] = "Hi ha conflictes amb altres versions. No s'han pogut guardar els canvis";
$lang['saved'] = "Els canvis s'han guardat correctament";
$lang['section_saved'] = "S'han guardat els canvis de la secció %s";
$lang['deleted'] = "La pàgina %s ha estat eliminada";
$lang['pageNotFound'] = "La pàgina %s no s'ha trobat";
$lang['pageExists'] = "La pàgina %s ja existeix";
$lang['metaEditForm'] = "Camps Edició";

$lang['auth_CreatePage'] = "No teniu permís per a Crear la pàgina %s";
$lang['auth_EditPage'] = "No teniu permís per a Editar la pàgina %s";
$lang['auth_ViewPage'] = "No teniu permís per a Veure la pàgina %s";
$lang['auth_WritePage'] = "No teniu permís per a Escriure la pàgina %s";
$lang['auth_DeletePage'] = "No teniu permís per a Eliminar la pàgina %s";
$lang['auth_DeleteResource'] = "No teniu permís per a Eliminar el recurs %s";
$lang['auth_UploadMedia'] = "No teniu permís per a pujar fitxers";
$lang['auth_TokenNotVerified'] = "Token no verificat";
$lang['auth_UserNotAuthenticated'] = "Usuari no autenticat";
$lang['auth_CommadNotAllowed'] = "Comanda no permesa";
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

$lang['tab_shortcuts'] = "Dreceres";
$lang['changes_type_filter'] = "Filtre dels canvis recents";
$lang['changes_navigation'] = "Paginació dels canvis recents";
$lang['recent_controls'] = "Controls dels canvis recents";
$lang['recent_list_loaded'] = "Llista dels canvis recents carregada";
$lang['recent_list'] = "canvis recents";

// Avisos del sistema
$lang['system_warning_default_title'] = 'Avís del sistema';

// Plantilles
$lang['plantilles:user:dreceres'] = "Nou document de dreceres.\n\n"
    . "Per afegir dreceres a documents o espais de noms, afegir enllaços al document o a l'espai de noms, seguint la sintaxi wiki. Per exemple:\n\n"
    . "[[wiki:navigation|Drecera a la documentació de la wiki]]";
//Excepciones del proyecto 'defaultProject'
$lang['pageExist'] = 'The page %s already exists';
$lang['commandAuthorizationNotFound'] = 'Command authorization not found';
$lang['cantCreatePageInProject'] = 'No pots crear una pàgina en aquest projecte';
$lang[''] = '';
$lang[''] = '';
$lang[''] = '';

//Excepciones del proyecto 'documentation'
$lang['projectException']['projectExist'] = 'The project %s already exist';
$lang['projectException']['projectNotExist'] = 'The project %s not already exist';
$lang['projectException']['unknown'] = 'Unknown project exception';
$lang['projectException']['userNotAuthorized'] = 'Vosté no té permís a %s';
$lang['projectException']['authorNotVerified'] = 'Vosté no té permís a %s';
$lang['projectException']['responsableNotVerified'] = 'Vosté no té permís a %s';
$lang['projectException']['insufficientPermissionToEditProject'] = 'Vosté no té permís per editar aquest projecte %s';
$lang['projectException']['insufficientPermissionToCreateProject'] = 'Vosté no té permís per crear el projecte %s aquí';
$lang['projectException']['insufficientPermissionToDeleteProject'] = 'Vosté no té permís per eliminar el projecte %s';
$lang['projectException']['insufficientPermissionToGenerateProject'] = 'Vosté no té permís per generar el projecte %s';

$lang['title_message_notification'] = 'Missatge de %s';
$lang['title_message_notification_with_id'] = 'Missatge de %s (document %s)';
$lang['doc_message'] = 'Missatge referent a: [[%s|%s]].';

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
