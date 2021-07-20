<?php

namespace Appwrite\SDK\Language;

class iOS extends Swift {

    /**
     * @return string
     */
    public function getName()
    {
        return 'iOS';
    }

    public function getFiles()
    {
        return array_merge(parent::getFiles(), [
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/WebAuthComponent.swift',
                'template'      => '/swift/Sources/WebAuthComponent.swift.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/Error.swift',
                'template'      => '/swift/Sources/Error.swift.twig',
                'minify'        => false,
            ],
            [
                'scope'         => 'default',
                'destination'   => '/Sources/{{ spec.title | caseUcfirst}}/View+OAuth.swift',
                'template'      => '/swift/Sources/View+OAuth.swift.twig',
                'minify'        => false,
            ],
        ]);
    }
}