// This is for the  old  admin setting - delete this  file  when done 
jQuery(document).ready(() => {
    function katorymnd_deki(zfkn, ojv, qldk, npj, hwjd, hbz) {
        jQuery(zfkn).click(() => {
            jQuery(ojv).show();
            jQuery(qldk).hide(); //hide id
            jQuery(npj).hide(); //hide id
            jQuery(hwjd).hide(); //hide id setting dettails
            jQuery(hbz).hide(); //hide id settings details
        });
    }

    dwks = katorymnd_deki("#katorymnd_gvrn", "#vtwq", '#yuor');
    mwp = katorymnd_deki("#katorymnd_nxct", "#yuor", '#vtwq');
});


