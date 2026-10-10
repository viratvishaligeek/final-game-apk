<footer class="site-footer">
    <div class="footer-top wrap">
        <div class="footer-brand-block"><a class="brand" href="{{ route('index') }}"><span class="brand-emblem">786</span><span class="brand-word">SATTA<span>786</span><small>RESULTS • RECORDS • CHARTS</small></span></a><p>A structured reference for published market results, schedules, and historical records.</p></div>
        <div><h3>Explore</h3><a href="{{ route('index') }}#today-results">Today's results</a><a href="{{ route('index') }}#markets">Market board</a><a href="{{ route('index') }}#records">Old records</a><a href="{{ route('index') }}#market-charts">Year-wise charts</a></div>
        <div><h3>Information</h3><a href="{{ route('information',['page'=>'about']) }}">About</a><a href="{{ route('information',['page'=>'faq']) }}">FAQ</a><a href="{{ route('information',['page'=>'contact']) }}">Contact</a><a href="{{ route('information',['page'=>'privacy-policy']) }}">Privacy policy</a></div>
        <div><h3>Policies</h3><a href="{{ route('information',['page'=>'terms-and-conditions']) }}">Terms & conditions</a><a href="{{ route('information',['page'=>'disclaimer']) }}">Disclaimer</a><p class="footer-note">Information only. No prediction or financial outcome is guaranteed.</p></div>
    </div>
    <div class="footer-bottom"><div class="wrap"><span>© {{ date('Y') }} Satta 786</span><span>Use responsibly • Follow applicable local laws</span></div></div>
</footer>