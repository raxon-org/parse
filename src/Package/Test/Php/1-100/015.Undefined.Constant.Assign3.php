<?php
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

use Error;
use ErrorException;
use Exception;
use ParseError;

class Temp {

    /**
     * @throws Error
     * @throws ErrorException
     * @throws Exception
     * @throws ParseError
     */
    public function run(): void
    {
        try {
            $iefakbbj_mfep_kknn_aiee_djnkdkdaljpm = __DIR__;
            $opinffke_lmap_klie_aibc_picoocemenfd = __FILE__;
            $gdgbemdf_mgok_keec_apba_bpgljbmamomb = __NAMESPACE__;
            $eheekhmh_mjol_klmh_omgd_hphgidkdlggl = __CLASS__;
            $eelmggac_bolk_knif_pfpm_nacddngdmfob = '__METHOD__';
            $lgeagbfe_anmg_khhl_bgmf_gmceogidcnhh = __CLASS__2;
        } catch(Error | ErrorException | Exception | ParseError $exception) {
            throw $exception;
        }
    }
}

$obj = new Temp();
$obj->run();