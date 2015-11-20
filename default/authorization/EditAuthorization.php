<?php
/**
 * EditAuthorization crea los objetos de autorización del comandos Edit
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC') ) die();

class EditAuthorization extends CommandAuthorization {
    
//    public function CanRun($dokuModel) {
//        $ret = NULL;
//        if(!$dokuModel->authenticatedUsersOnly
//            || $dokuModel->isSecurityTokenVerified()
//            && $dokuModel->isUserAuthenticated()
//            && $dokuModel->isAuthorized()
//        ) {
//            $ret = $dokuModel->getResponse();
//
//            if($dokuModel->modelWrapper->isDenied()) {
//                $dokuModel->error        = 403;
//                $dokuModel->errorMessage = "permission denied";
//            }
//        } else {
//            //TODO[xavi] Per poder fer proves deshabilitem la comprovació
//            $dokuModel->error        = 403;
//            $dokuModel->errorMessage = "permission denied";
//
//        }
//        if($dokuModel->error && $dokuModel->throwsException) {
//            throw new Exception($dokuModel->errorMessage);
//        }
//        return $ret;
//    }
}
