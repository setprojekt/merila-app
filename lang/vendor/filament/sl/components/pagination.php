<?php

return [

    'label' => 'Navigacija po straneh',

    'overview' => '{1} Prikazuje se 1 rezultat|[2,*] Prikazuje se :first do :last od skupno :total rezultatov',

    'fields' => [

        'records_per_page' => [

            'label' => 'Na stran',

            'options' => [
                'all' => 'Vsi',
            ],

        ],

    ],

    'actions' => [

        'first' => [
            'label' => 'Prva',
        ],

        'go_to_page' => [
            'label' => 'Pojdi na stran :page',
        ],

        'last' => [
            'label' => 'Zadnja',
        ],

        'next' => [
            'label' => 'Naprej',
        ],

        'previous' => [
            'label' => 'Nazaj',
        ],

    ],

];
