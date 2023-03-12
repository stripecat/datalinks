<html>

<head>
    <script type="text/javascript" src="/js/get-jwt.js"></script>
    <script type="text/javascript" src="/js/base_functions.js"></script>
    <script src="/js/vue-toast-notification.js"></script>
    <link href="/css/theme-sugar.css" rel="stylesheet">
    <script src="/js/vue-multiselect.min.js"></script>
    <link rel="stylesheet" href="/js/vue-multiselect.min.css">
    <script src="/js/vue.js"></script>

    <style>
        .loader {
            border: 16px solid #f3f3f3;
            border-radius: 50%;
            border-top: 16px solid #3498db;
            width: 10px;
            height: 10px;
            -webkit-animation: spin 2s linear infinite;
            /* Safari */
            animation: spin 2s linear infinite;

        }

        .moleanttable {
            border-collapse: collapse;
            width: 95%;
            margin: 8px;

        }

        .moleanttable td,
        #customers th {
            border: 1px solid #ddd;
            padding: 4px;
        }

        .moleanttable tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .moleanttable tr:hover {
            background-color: #ddd;
        }

        .moleanttable th {
            padding: 4px;
            text-align: left;
            background-color: white;
            color: black;
        }

        /* Safari */
        @-webkit-keyframes spin {
            0% {
                -webkit-transform: rotate(0deg);
            }

            100% {
                -webkit-transform: rotate(360deg);
            }
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>

    <style id="kirki-inline-styles">
        body,
        html {
            font-family: Open Sans;
            font-weight: 400;
        }

        strong {
            font-family: Open Sans;
            font-weight: 700;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        .qt-btn,
        .qt-capfont,
        caption,
        .qt-title {
            font-family: Khand;
            font-weight: 500;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .qt-menubar-top,
        .qt-menubar,
        .qt-side-nav,
        .qt-menu-footer {
            font-family: Khand;
            font-weight: 400;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

 
    </style>

    <script>


        function epochtodate(ts) {
            const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            d = new Date(ts * 1000);
            return d.toLocaleString("en-US");
        }

        function decodeHtml(html) {
            html = html.replace(/&#37;/g, "%");
            return decodeURIComponent(html.replace(/\+/g, ' '));
        }

        function setspin(state) {
            var x = document.getElementById("spinner");
            if (state != "off") {

                x.style.display = "block";
            } else {

                x.style.display = "none";
            }
        }

    </script>
</head>

<body>
    <form>
        <p>
        <table>
            <tr>
                <td>
                    <label for="requester">Your name:</label>
            </tr>
            </td>
            <tr>
                <td>
                    <input type="text" tabindex="1" id="requester" name="requester" onkeyup="openreq()" autofocus>
            </tr>
            <tr></tr>
            </td>

            <tr>
                <td>
                    <label for="greeting">Greeting (optional). Not read on the radio:</label>
            </tr>
            </td>

            <tr>
                <td>
                    <textarea id="greeting" tabindex="2" name="greeting"
                        style=" min-width:600px; max-width:100%;min-height:50px;height:100%;width:100%;"></textarea>
                </td>
            </tr>
            <tr></tr>
            <tr>
                <td> <label for="request">Song to request:</label></td>
            </tr>
            <td>
                <table>

                    <tr>
                        <td width="600">
                            <div id="systems-app">
                                <input type="hidden" id="selsong" name="selsong" value="">
                                <multiselect v-model="value" placeholder="" label="name" track-by="code" id="tracksel"
                                    :options="options" :multiple="false" :tabindex="3" @select="onSelect"
                                    @remove="onRemove" @search-change="onSearch">

                                    <span slot="noResult">

                                    </span>
                                    <span slot="noOptions">
                                        No options available
                                    </span>
                                </multiselect>
                        </td>
                        <td>
                            <div id="spinner" class="loader"></div>
                        </td>
                    </tr>

                </table>


                </div>


            </td>

            </tr>

            <tr>
                <td>

                    <button type="button" tabindex="4"
                        onclick="requesttrack(document.getElementById('selsong').value);">Request song</button>
                </td>
            </tr>
        </table>

        </p>




        </table>



        <script>


            function openreq() {
                if (document.getElementById("requester").value === "") {
                    document.getElementById('tracksel').disabled = true;
                    document.getElementById('tracksel').placeholder = "Type in your name first before selecting a track."
                    document.getElementById('tracksel').hidden = true;

                } else {
                    document.getElementById('tracksel').disabled = false;
                    document.getElementById('tracksel').placeholder = "Search for a track"
                    document.getElementById('tracksel').hidden = false;


                }
            }


            var vm = new Vue({
                el: '#systems-app',
                components: {
                    Multiselect: window.VueMultiselect.default
                },
                data() {
                    return {
                        // Value => what has been choosen. Only needed for multiselect.
                        value: [
                        ],
                        options: [

                        ]

                    }
                },
                methods: {

                    onSearch: async (searchQuery) => {

                        sdata = searchQuery;

                    },
                    onSelect: (selectedOption, id) => {

                        document.getElementById("selsong").value = selectedOption.code;
                    },
                    onRemove: (selectedOption, id) => {

                    }
                }
            });


        </script>
        </p>

        <p>
            If you create a new request right now:<br>
            Your expected place in the queue: <i id="qplace">-</i><br>
            Your request can be expected to play within: <i id="qtime">-</i>. (+/- 15 minutes)

        </p>
        <p>
            The following songs are waiting to be played on the station.
            Requests are played two times per hour, at around 10 minutes and 40 minutes past the hour.
        </p>

        <p>
        <table class="moleanttable" id="qreqs">

        </table>
        </p>
        <p>
            The following songs has previously been played recently.
        </p>
        <p>
        <table class="moleanttable" id="qplays">

        </table>
        </p>

    </form>

    <script>

        var lastword = "";

        function getrequeststats() {

            var client = new HttpClient();
            client.get('/get-token/', function (response) {
                var z = "";
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function () {


                    if (this.readyState == 4 && this.status == 200) {

                        jsonresponse = JSON.parse(this.responseText);

                        document.getElementById("qplace").innerHTML = jsonresponse.RequestStatistics[0].YourQueueNumber;
                        document.getElementById("qtime").innerHTML = jsonresponse.RequestStatistics[0].ExpectedWaitingTime;

                        z = "<tr><th>#</th><th>Song</th><th>Requested</th><th>Source</th><th>Status</th></tr>";
                        y = "<tr><th>#</th><th>Song</th><th>Requested</th><th>Source</th><th>Status</th></tr>";
                        var w = 0;
                        for (j in jsonresponse.CurrentRequests) {


                            if (jsonresponse.CurrentRequests[j].State == "Waiting") {
                                z += "<tr><td>" + (Number(j) + 1) + "</td>"
                                z += "<td>" + decodeHtml(jsonresponse.CurrentRequests[j].Song) + "</td><td>";
                                z += epochtodate(jsonresponse.CurrentRequests[j].Requested) + "</td>";
                                z += "<td>" + decodeHtml(jsonresponse.CurrentRequests[j].Source) + "</td>";
                                z += "<td>" + decodeHtml(jsonresponse.CurrentRequests[j].State) + "</td></tr>";
                                w++;
                            }
                            else {
                                y += "<tr><td>" + (Number(j) + 1) + "</td>"
                                y += "<td>" + decodeHtml(jsonresponse.CurrentRequests[j].Song) + "</td><td>";
                                y += epochtodate(jsonresponse.CurrentRequests[j].Requested) + "</td>";
                                y += "<td>" + decodeHtml(jsonresponse.CurrentRequests[j].Source) + "</td>";
                                y += "<td>" + decodeHtml(jsonresponse.CurrentRequests[j].State) + "</td></tr>";
                            }

                        }

                        if (w != 0) { document.getElementById("qreqs").innerHTML = z; } else {
                            z += "<tr><td>-</td><td>No songs are waiting to be played</td><td>-</td><td>-</td><td>-</td></tr>";
                            document.getElementById("qreqs").innerHTML = z;
                        }
                        document.getElementById("qplays").innerHTML = y;


                    }
                    else if (this.readyState == 4 && this.status == 400) {

                    }
                };

                xhttp.open("POST", "http://api.domain.tld/radio/getrequeststats/", true);
                xhttp.send("{\"Password\":\"" + response + "\", \"StationID\":\"" + "1" + "\"}");

            });

        }

        function requesttrack(TrackID) {

            requester = document.getElementById("requester").value
            greeting = document.getElementById("greeting").value

            var client = new HttpClient();
            client.get('/get-token/', function (response) {
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function () {

                    if (this.readyState == 4 && this.status == 200) {

                        jsonresponse = JSON.parse(this.responseText);
                        Vue.$toast.success("Request successfully added.");
                        getrequeststats();
                    }
                    else if (this.readyState == 4 && this.status == 400) {
                        jsonresponse = JSON.parse(this.responseText);
                        Vue.$toast.error(jsonresponse.message + ': ' + jsonresponse.submessage);
                    }
                };

                xhttp.open("POST", "http://api.domain.tld/radio/addrequest/", true);
                xhttp.send("{\"Password\":\"" + response + "\", \"TrackID\":\"" + TrackID + "\", \"StationID\":\"" + "1" + "\", \"Requester\":\"" + requester + "\", \"Source\": \"Internet\", \"Greeting\":\"" + greeting + "\"}");

            });

        }
        function debounce(func, wait, immediate) {
            var timeout;
            return function () {
                var context = this, args = arguments;
                var later = function () {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                var callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        };


        var firesearch = debounce(function () {
            // All the taxing stuff you do

            if (sdata != "") {
                vm.loading = true;
                searchtracks(sdata);
            }
            if (adata != "") {
                vm.loading = false;
                vm.options = adata
            }

        }, 250);

        document.addEventListener('keyup', firesearch);
        var adata = [{ name: ".", code: "0" }]

        function searchtracks(search) {

            if (search == "") { document.getElementById("searchres").innerHTML = "<option value=\"0\">-</option>"; } else {

                var client = new HttpClient();
                client.get('/get-token/', function (response) {

                    var xhttp = new XMLHttpRequest();
                    var searchresponse = "";
                    var menusearchresults = "";
                    var searchresults = "";

                    document.getElementById('tracksel').setAttribute('loading', "true");

                    setspin("on");

                    xhttp.onreadystatechange = function () {


                        if (this.readyState == 4 && this.status == 200) {

                            jsonresponse = JSON.parse(this.responseText);

                            adata.length = 0

                            var postpend = "";
                            var prepend = "";

                            for (i in jsonresponse.Tracks) {

                                if (jsonresponse.Tracks[i].Title != "") {

                                    postpend = "";
                                    prepend = "";


                                    if (jsonresponse.Tracks[i].TrackCanBeRequested == 0) {
                                        prepend = "(x) ";
                                        postpend = "";
                                    }
                                    searchresponse = decodeHtml(prepend + jsonresponse.Tracks[i].Artist) + " - " + decodeHtml(jsonresponse.Tracks[i].Title + postpend);
                                    searchid = jsonresponse.Tracks[i].TrackID;

                                }


                                if (jsonresponse.Tracks[i].RequestVerdict != "Artist filtered") {
                                    adata.push({ name: searchresponse, code: searchid })

                                }


                            }

                            // if (i == 0) { adata = [{ name: "-", code: "0" }]; }
                            setspin("off");
                        }
                        else if (this.readyState == 4 && this.status == 400) {

                            setspin("off");

                        }

                    };

                    xhttp.open("POST", "http://api.domain.tld/radio/gettrack/", true);
                    xhttp.send("{\"Password\":\"" + response + "\", \"Search\":\"" + search + "\", \"SimpleSearch\":\"1\", \"StationID\":\"1\", \"EligibilityFilter\":\"0\", \"ReturnArtistDescription\":\"0\", \"ReturnArtistLongDescription\":\"0\"}");

                });
            }

        }

        //var myVar2 = setInterval(searchtracks(document.getElementById('search').value), 1000);

        setspin("off");

        //vm.setAttribute('loading',true);
        openreq();
        var myVar3 = setInterval(getrequeststats, 10000);
        var sdata = "";
        var myVar4 = getrequeststats();

        Vue.use(VueToast, {
            position: 'top'
        });

    </script>

</body>

</html>