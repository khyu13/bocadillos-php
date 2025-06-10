<?php
    function hoy($fecha){
        switch($fecha) {
            case 'Monday':
                return 'Lunes';
            case 'Tuesday':
                return 'martes';
            case 'Wednesday':
                return 'miércoles';
            case 'Thursday':
                return 'jueves';
            case 'Friday':
                return 'viernes';
            default:
                return '';
        }

    }
?>