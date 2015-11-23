<?php

class ThemeHouse_Warnings_Listener_FileHealthCheck
{

    public static function fileHealthCheck(XenForo_ControllerAdmin_Abstract $controller, array &$hashes)
    {
        $hashes = array_merge($hashes,
            array(
                'library/ThemeHouse/Warnings/DataWriter/WarningGroup.php' => 'e3030f314aa86c605da8f7e829735496',
                'library/ThemeHouse/Warnings/Extend/XenForo/ControllerAdmin/Warning.php' => '1eba5e2f2e4e43a7e93370f743a08b0c',
                'library/ThemeHouse/Warnings/Extend/XenForo/ControllerPublic/Member.php' => '545429012cc591ed2f25eb4685a0adbb',
                'library/ThemeHouse/Warnings/Extend/XenForo/ControllerPublic/Warning.php' => 'b62ed65ceb064a53764144c7abc283bf',
                'library/ThemeHouse/Warnings/Extend/XenForo/DataWriter/Warning.php' => 'd21804d4b959362fe03df30d1834e383',
                'library/ThemeHouse/Warnings/Extend/XenForo/DataWriter/WarningAction.php' => '438d50dacf7eb4c6d10e1fd919d29eba',
                'library/ThemeHouse/Warnings/Extend/XenForo/DataWriter/WarningDefinition.php' => 'e2c1fa4fad2b27e2387efaf81bc79bd8',
                'library/ThemeHouse/Warnings/Extend/XenForo/Model/Warning.php' => 'e2b70255707b0f9c71b27d63a1b95907',
                'library/ThemeHouse/Warnings/Extend/XenForo/ViewPublic/Member/WarnFill.php' => '98a7d8b2c980180cae0e19b60b11671c',
                'library/ThemeHouse/Warnings/Install/Controller.php' => '2362f4973de8d2323e739111e4451635',
                'library/ThemeHouse/Warnings/Listener/ContainerAdminParams.php' => '8c33b2b584ce862ae7548ab1d1a2ce85',
                'library/ThemeHouse/Warnings/Listener/LoadClass.php' => '835f8a43c656c54d2bf8a0f5302e8bba',
                'library/ThemeHouse/Warnings/Listener/TemplateModification.php' => '7d665986cee11e51884be8dda992bc63',
                'library/ThemeHouse/Warnings/Listener/TemplatePostRender.php' => 'afdc7567eeb84b85c63e7b9640017dd3',
                'library/ThemeHouse/Warnings/Option/PointsExpiry.php' => '9d2fd4642cc8adaec6ba49be946756cd',
                'library/ThemeHouse/Warnings/Template/Helper/WarningGroup.php' => 'aece7830ed3b7719df6b01e1b37dfb0e',
                'library/ThemeHouse/Install.php' => '18f1441e00e3742460174ab197bec0b7',
                'library/ThemeHouse/Install/20151109.php' => '2e3f16d685652ea2fa82ba11b69204f4',
                'library/ThemeHouse/Deferred.php' => 'ebab3e432fe2f42520de0e36f7f45d88',
                'library/ThemeHouse/Deferred/20150106.php' => 'a311d9aa6f9a0412eeba878417ba7ede',
                'library/ThemeHouse/Listener/ControllerPreDispatch.php' => 'fdebb2d5347398d3974a6f27eb11a3cd',
                'library/ThemeHouse/Listener/ControllerPreDispatch/20150911.php' => 'f2aadc0bd188ad127e363f417b4d23a9',
                'library/ThemeHouse/Listener/InitDependencies.php' => '8f59aaa8ffe56231c4aa47cf2c65f2b0',
                'library/ThemeHouse/Listener/InitDependencies/20150212.php' => 'f04c9dc8fa289895c06c1bcba5d27293',
                'library/ThemeHouse/Listener/ContainerParams.php' => '43bf59af9f140f58f665be373ac07320',
                'library/ThemeHouse/Listener/ContainerParams/20150106.php' => '36fa6f85128a9a9b2b88210c9abe33bd',
                'library/ThemeHouse/Listener/LoadClass.php' => '5cad77e1862641ddc2dd693b1aa68a50',
                'library/ThemeHouse/Listener/LoadClass/20150518.php' => 'f4d0d30ba5e5dc51cda07141c39939e3',
                'library/ThemeHouse/Listener/Template.php' => '0aa5e8aabb255d39cf01d671f9df0091',
                'library/ThemeHouse/Listener/Template/20150106.php' => '8d42b3b2d856af9e33b69a2ce1034442',
                'library/ThemeHouse/Listener/TemplateModification.php' => '81c3b03dde794c2c236c171fc01c6232',
                'library/ThemeHouse/Listener/TemplateModification/20150106.php' => '31f904c658841f2d45cf3163aab70bd0',
                'library/ThemeHouse/Listener/TemplatePostRender.php' => 'b6da98a55074e4cde833abf576bc7b5d',
                'library/ThemeHouse/Listener/TemplatePostRender/20150106.php' => 'efccbb2b2340656d1776af01c25d9382',
            ));
    }
}