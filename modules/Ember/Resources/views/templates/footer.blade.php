<!-- footer -->
<div id="footer">
    <div class="em-foot">
        <ul id="footer-pages" class="nav">
            <li v-for="page in pages" class="nav-item" v-cloak><a :href="'/pages/' + page.id">@{{ page.title }}</a></li>
        </ul>
    </div>
    <div class="copyright">&copy; {{ date('Y') }} {{ config('global.site_name', 'iMenu') }}</div>
</div>
