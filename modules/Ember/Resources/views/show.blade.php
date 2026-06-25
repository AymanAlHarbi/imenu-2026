<!DOCTYPE html>
<html>
 @include('ember::templates.head')
<body>
    <?php
        function clean($string) {
            $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

            return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
        }
    ?>
    @include('ember::templates.mobile-menu')
    <div id='wrapper'>
         @include('ember::templates.header')
         @include('ember::templates.modals')
         @include('ember::templates.call_waiter')
         @include('ember::templates.links')
         @include('ember::templates.place-content')
         @if (isset($doWeHaveImpressumApp)&&$doWeHaveImpressumApp&&strlen($restorant->getConfig('impressum_value',''))>5)
            @include('ember::templates.impressum')
        @endif
        @include('ember::templates.footer')
         
    </div>
   
 
    @include('ember::templates.scripts')
    
    
</body>

</html>