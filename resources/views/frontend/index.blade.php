@extends('frontend.include.app')
@section('content')
    {{-- top box ad start --}}
    <section class="boxc3">
        <div
            style="background-image: linear-gradient(gold 60%, #30e0f2); font-weight: bold; border-width: 3px; border-color:#000; border-style: outset; padding: 10px; border-radius: 10px; text-align: center;">
            <font style="color:red;font-size:20px">
                PLAY ONLINE</font><br>
            <font style="color:black; font-size:14px">



                सीधे सट्टा कंपनी का 𝐍𝐨 𝟏 खाईवाल<br>
                *🏆 AMAN 𝐁𝐇𝐀𝐈 𝐊𝐇𝐀𝐈𝐖𝐀𝐋🏆*<br>
                ♦️ *दिल्ली बाजार* ————— 𝟬𝟯:𝟬𝟬 𝗣𝗠<br>
                ♦️ *श्री गणेश* ——————— 𝟬𝟰:4𝟬 𝗣𝗠<br>
                ♦️ *फरीदाबाद* —–———— 𝟬6:0𝟬 𝗣𝗠<br>
                ♦️ *गाजियाबाद* ————— 𝟬𝟵:5𝟬 𝗣𝗠<br>
                ♦️ *गली* ————————— 𝟭𝟭:55 𝗣𝗠<br>
                ♦️ *दिसावर* ——————— 𝟬4:𝟬𝟬 𝗔𝗠<br>

                ((जोड़ी रेट 𝟏𝟎𝟎=𝟗7𝟎𝟎/-👈🥳💰<br>
                ((हर्फ रेट 𝟏𝟎𝟎𝟎=𝟗7𝟎𝟎/-👈🥳💰<br>
                नाम और काम दोनों का ब्रांड एक बार <br>सेवा का मोका जरूर<br> दे"𝐃𝐇𝐀𝐍𝐘𝐀𝐕𝐀𝐀𝐃🙏<br>
                9120196528<br>

            </font><br>
            <font style="color:red;font-size:25px">09120196528</font><br>
            <a href="https://t.me/+JIJNYLNuHqQ3ZmRl"><button
                    style="height:35px; width:300px; background-color:blue; color:#fff">
                    <font size="4px"><b>JOIN TELEGRAM</b></font>
                </button></a><br>
            <a href="whatsapp://send?text=Hello Sir!&phone=+919120196528"><button
                    style="height: 30px; width: 120px; background-color: #FFF; color: #000;"><span
                        style="font-size: large;"><b>WHATSAPP</b></span></button></a>
            <a href="tel:9120196528"><button style="height:30px; width:120px; background-color:green; color:#fff">
                    <font size="4px"><b>CALL NOW</b></font>
                </button></a>
            <br>
        </div>
    </section>
    {{-- top box ad end --}}

    {{-- Result today top start --}}
    <section id="results">
        <div class="result-heading">
            <a href="index.php">SATTA786 Satta King</a>
            <p>दिल्ली सत्ता | दिल्ली सट्टा दिसावर | दिल्ली सट्टा किंग</p>
        </div>

        <div class="resultmain">
            <p class="resultmaintime">{{ now()->format('d F Y h:i A') }}</p>
            <p class="resultmaintoday">Live Satta King Results – लाइव सट्टा किंग रिजल्ट</p>

            @foreach ($frontendResults as $game)
                <p class="livegame">{{ $game['name'] }}</p><br>
                <p class="liveresult">{{ $game['today'] }}</p><br>
            @endforeach
        </div>
    </section>

    {{-- Result today top start --}}

    <!-- Quick Link: Guessing Forum (after result, before records) -->
    <div class="result-bottom">
        <a href="satta-king-gali-satta-disawar-satta.php" title="Satta King 786 Guessing Forum">SATTA KING 786
            GUESSING FORUM</a>
    </div>

    <section id="markets-quick-record">
        {{-- chart link  --}}
        <div class="result-heading">
            <a href="chart.php" title="Satta King 786 Record Chart">TODAY LIVE SATTA KING 786 RESULT</a>
            <p>MARKET TIME • RECORD CHART • YESTERDAY / TODAY</p>
        </div>
        {{-- chart link  --}}

        {{-- middle box ad start --}}
        <style>
            .box {
                max-width: auto;
                width: 95%;
                margin: 8px auto;
                padding: 8px;
                background: #000;
                border: 3px solid #FFD700;
                border-radius: 16px;
                text-align: center;
                color: #FFD700;
                box-sizing: border-box;
                box-shadow: 0 0 15px #FFD700;
                animation: glow 1s infinite alternate
            }

            .heading {
                font-size: 18px;
                font-weight: 700;
                color: #fff;
                text-shadow: 0 0 8px red;
                margin-bottom: 6px
            }

            .line {
                font-size: 14px;
                font-weight: bold;
                margin: 2px 0
            }

            .rate {
                color: #00ff88;
                font-size: 15px;
                font-weight: bold;
                margin: 6px 0
            }

            .note {
                color: #ff4444;
                font-size: 13px;
                font-weight: bold;
                margin: 6px 0
            }

            .btn-wrap {
                display: flex;
                gap: 8px;
                justify-content: center
            }

            .btn {
                flex: 1;
                max-width: 140px;
                padding: 8px 0;
                border-radius: 25px;
                text-decoration: none;
                font: bold 14px;
                color: #fff
            }

            .call {
                background: #ff4444
            }

            .whatsapp {
                background: #25D366
            }

            @keyframes glow {
                from {
                    box-shadow: 0 0 8px #FFD700
                }

                to {
                    box-shadow: 0 0 20px #FFD700
                }
            }
        </style>

        <div class="box">
            <div class="heading">🔥 ONLINE KHAIWAL 🔥</div>

            <div class="line">VARUN भाई सट्टा खाईवाल</div>
            <div class="line">हिमाचल _ 2:00PM</div>
            <div class="line">दिल्ली बाजार • 3:00 PM</div>
            <div class="line">श्री गणेश • 4:30 PM</div>
            <div class="line">फरीदाबाद • 5:55 PM</div>
            <div class="line">गाजियाबाद • 9:30 PM</div>
            <div class="line">गली • 11:30 PM</div>
            <div class="line">दिसावर • 4:30 AM</div>

            <div class="rate">पेमेंट रेट</div>
            <div class="line">जोड़ी रेट : 10 के 980</div>
            <div class="line">हर्फ रेट : 100 के 980</div>

            <div class="note">⚡ सुपर फास्ट पेमेंट का वादा ⚡</div>

            <div class="btn-wrap">
                <a class="btn call" href="tel:+917496087518">📞 Call</a>
                <a class="btn whatsapp" href="https://wa.me/917496087518" target="_blank">💬 WhatsApp</a>
            </div>
        </div>
        {{-- middle box ad end --}}

        {{-- market table with result  --}}
        <div class="seo-box quick-record-box">
            <div style="overflow:auto;">
                <table class="quick-record-table">
                    <thead>
                        <tr>
                            <th>MARKET</th>
                            <th>TIME</th>
                            <th>RECORD</th>
                            <th>YESTERDAY</th>
                            <th>TODAY</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($frontendResults as $game)
                            <tr class="">
                                <td class="qr-market">{{ $game['name'] }}</td>

                                <td class="qr-time">{{ $game['time'] }}</td>

                                <td class="qr-link">
                                    <a href="{{ url('/' . $game['slug'] . '-satta-786.php') }}">
                                        Record Chart
                                    </a>
                                </td>

                                <td class="qr-val">
                                    {{ $game['yesterday'] }}
                                </td>

                                <td class="qr-val">
                                    {{ $game['today'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        {{-- market table with result  --}}
    </section>

    {{-- result box start --}}
    <section id="markets">
        <div class="market-list">
            @foreach ($marketResults as $game)
                <div class="market-card {{ $loop->first ? 'market-card--full' : '' }}">
                    <div class="market-card-head">
                        <div class="market-card-name">
                            <a href="{{ url('/' . $game['slug'] . '-satta-786.php') }}"
                                title="{{ $game['name'] }} Gali Disawar Record">
                                {{ $game['name'] }}
                            </a>
                        </div>

                        <div class="market-card-time">
                            ({{ $game['time'] }})
                        </div>
                    </div>

                    <div class="market-card-body">
                        <div class="market-card-res">
                            { {{ $game['yesterday'] }} }

                            <span class="market-arrow">➜</span>

                            [ {{ $game['today'] }} ]
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- result box start --}}



    {{-- middle second ad start --}}
    <section class="boxc3">
        <div
            style="background-image: linear-gradient(pink 60%, #30e0f2); font-weight: bold; border-width: 3px; border-color:#000; border-style: outset; padding: 10px; border-radius: 10px; text-align: center;">
            <font style="color:red;font-size:20px">
                PLAY ONLINE</font><br>
            <font style="color:blue; font-size:14px">



                सीधे सट्टा कंपनी का 𝐍𝐨 𝟏 खाईवाल<br>
                *🏆 POOJA MADAM🏆*<br>
                ♦️ *दिल्ली बाजार* ————— 𝟬𝟯:𝟬𝟬 𝗣𝗠<br>
                ♦️ *श्री गणेश* ——————— 𝟬𝟰:𝟯𝟬 𝗣𝗠<br>
                ♦️ *फरीदाबाद* —–———— 𝟬𝟱:𝟱𝟬 𝗣𝗠<br>
                ♦️ *गाजियाबाद* ————— 𝟬𝟵:𝟮𝟬 𝗣𝗠<br>
                ♦️ *गली* ————————— 𝟭𝟭:𝟮𝟬 𝗣𝗠<br>
                ♦️ *दिसावर* ——————— 𝟬𝟮:𝟬𝟬 𝗔𝗠<br>

                ((जोड़ी रेट 𝟏𝟎𝟎=𝟗5𝟎𝟎/-👈🥳💰<br>
                ((हर्फ रेट 𝟏𝟎𝟎𝟎=𝟗5𝟎𝟎/-👈🥳💰<br>
                नाम और काम दोनों का ब्रांड एक बार <br>सेवा का मोका जरूर दे"<br>
                9053878009<br>

            </font><br>
            <font style="color:red;font-size:25px">08570860536</font><br>
            <a href="https://t.me/+JIJNYLNuHqQ3ZmRl"><button
                    style="height:35px; width:300px; background-color:blue; color:#fff">
                    <font size="4px"><b>JOIN TELEGRAM</b></font>
                </button></a><br>
            <a href="whatsapp://send?text=Hello Sir!&phone=+918570860536"><button
                    style="height: 30px; width: 120px; background-color: #FFF; color: #000;"><span
                        style="font-size: large;"><b>WHATSAPP</b></span></button></a>
            <a href="tel:8570860536"><button style="height:30px; width:120px; background-color:green; color:#fff">
                    <font size="4px"><b>CALL NOW</b></font>
                </button></a>
            <br>
        </div>
    </section>
    <div
        style="background-color: #FCDFFF;color:white;font-weight: bold;border: double 3px blue;padding: 5px;border-radius: 20px;text-align: center;">
        <p style=" font-size: 16px; color: black; ">सिंगल जोड़ी गली ओर देशावर में
            गेम 100% गारंटी से पास
            सट्टे की दुनिया में काम के साथ नाम भी चलता है!
            जितने लोग हमसे जुड़े सब मालामाल हुए हैं
            बड़े खिलाड़ी आज ही संपर्क करें!
        </p>
        <p style="font-size:20px; color:BLUE "> Satta King 𝐂,𝐌,𝐎)</p>
        <a href="https://whatsapp.com/channel/0029Vb8wEsIKAwEsEUURHi3V"><button
                style="height: 40px;width: 220px;background-color: green;color:#FFF;border: double 3px red;border-radius: 20px;">
                <font size="4px"><b>WHATSSAP NOW</b></font>
            </button></a>
    </div>
    {{-- middle second ad start --}}

    <!-- Reference-style Old Record / Charts -->
    <section id="records">
        <div class="result-heading">
            <a href="chart.php" title="Satta King Record Chart">
                Satta King Old Record — सट्टा किंग चार्ट रिकॉर्ड
                <br>{{ $recordYears ? min($recordYears) : date('Y') }} TO
                {{ $recordYears ? max($recordYears) : date('Y') }}
            </a>

            <p>DESAWER | FARIDABAD | GHAZIABAD | GALI</p>
        </div>
        <table class="hotlink yearline">
            @foreach ($recordYears as $year)
                <tr>
                    @foreach ($marketResults as $game)
                        <td>
                            <a href="{{ url('/' . $game['slug'] . '-charts/' . $year) }}"
                                title="{{ $game['name'] }} Satta Chart {{ $year }}">
                                {{ strtoupper($game['name']) }} Satta Chart {{ $year }}
                            </a>
                        </td>
                        @if ($loop->iteration % 8 === 0)
                </tr>
                @if (!$loop->last)
                    <tr>
                @endif
            @endif
            @endforeach
            </tr>
            @endforeach
        </table>
    </section>

    <!-- Reference-style Old Record / Charts -->


    <!-- Reference-style SEO Content + FAQ -->
    <section id="seo" style="margin-bottom:0;">
        <div class="result-heading">
            <a href="index.php" title="Gali Disawar Information">Gali Disawar INFORMATION</a>
            <p>Gali Disawar | SATTA KING 786 | Gali Disawar RESULT TODAY | CHART | RECORD</p>
        </div>

        <div class="seo-jump" aria-label="Quick jumps">
            <a href="#markets-quick-record">Today</a>
            <a href="#records">Old Record</a>
            <a href="chart.php">Chart</a>
            <a href="#faq">FAQ</a>
        </div>

        <div class="seo-box">
            <h2>DISCLAIMER – डिस्क्लेमर</h2>
            <p>Dear user, this site is made only for entertainment and informational purpose. satta786.com is not
                involved with any gambling activity. All data shown on the website is record/information. Please follow
                your country rules and laws. You are responsible for any gain or loss.</p>
            <p>प्रिय उपयोगकर्ता, यह साइट केवल मनोरंजन और जानकारी के लिए बनाई गई है। satta786.com किसी भी जुए की गतिविधि
                में शामिल नहीं है। वेबसाइट पर दिया गया डेटा केवल रिकॉर्ड/जानकारी है। कृपया अपने देश के नियमों का पालन
                करें। किसी भी लाभ या हानि के लिए आप स्वयं जिम्मेदार होंगे।</p>
            <p style="margin-top:10px;">Read full policy: <a href="disclaimer.php"><b>Disclaimer Page</b></a></p>
        </div>

        <div class="seo-box">
            <h2>How to use Satta King 786 Chart (Simple तरीका)</h2>
            <p>Chart/record ka use informational purpose ke liye होता है: aap year/month select karke old results check
                कर सकते हैं। “Today” aur “Yesterday” results compare करने के लिए ऊपर का quick record table देखें.</p>
            <p>Helpful: <a href="chart.php"><b>Satta King 786 Chart</b></a> • <a href="gali-satta-786.php"><b>Gali
                        Record (1 Year)</b></a> • <a href="disawar-satta-786.php"><b>Disawar Record (1 Year)</b></a>
            </p>
        </div>

        <div class="seo-box dark">
            <h2 style="color:#ff0;">SATTA KING DICTIONARY</h2>
            <p><b>Jodi</b>: any two-digit number 00–99.</p>
            <p><b>Haruf</b>: single digit of the result (ander/bahar).</p>
            <p><b>Munda</b>: represents 0 in the tens place (e.g., 01).</p>
        </div>

        <div class="seo-box" id="faq">
            <h2>FREQUENTLY ASKED QUESTIONS (FAQ)</h2>

            <div class="faq-accordion">
                <details class="faq-item">
                    <summary>Q1: Gali Disawar result today kahan milega?</summary>
                    <div class="faq-answer">Homepage par “Today Live Satta King 786 Result” section me latest update
                        मिलता है. Record ke liye <a href="chart.php"><b>Satta King 786 Chart</b></a> देखें.</div>
                </details>

                <details class="faq-item">
                    <summary>Q2: Satta King 786 live result ka matlab kya hai?</summary>
                    <div class="faq-answer">Live result ka मतलब latest updated market result. Is page par aapko
                        Today/Yesterday columns ke साथ fast update दिखता है.</div>
                </details>
            </div>
        </div>

        <!-- JSON-LD for FAQs (kept for SEO) -->
        {{-- <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "FAQPage",
      "mainEntity": [
        {
          "@type": "Question",
          "name": "Gali Disawar result today kahan milega?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Homepage par Today Live Satta King 786 Result section me latest update milta hai. Record ke liye Satta King 786 Chart dekhein."
          }
        },
        {
          "@type": "Question",
          "name": "Satta King 786 live result ka matlab kya hai?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Live result ka matlab latest updated market result. Is page par Today/Yesterday columns ke saath fast update dikhaya jata hai."
          }
        }
      ]
    }
    </script> --}}
    </section>
@endsection
