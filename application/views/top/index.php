<!DOCTYPE html>
<html lang="ja">
<head>
    <title>過去のアメダス・ライデンデータ検索</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="Keywords" content="気象庁,過去の雷観測,雷観測" />
    <meta name="Description" content="過去のアメダス・ライデンデータを検索できます。" />
    <!-- <meta name="viewport" content="width=device-width,initial-scale=1"> -->

    <link rel="icon" href="<?php echo $this->config->item('base_url') ?>/img/favicon.ico" type="image/x-icon" />
    <link rel="shortcut icon" href="<?php echo $this->config->item('base_url') ?>/img/favicon.ico" type="img/x-icon" />
    <link href="<?php echo $this->config->item('base_url') ?>/css/top/index220403.css" rel="stylesheet">

    <!-- Vue.js -->
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.11"></script>
    
    <!-- html2canvas -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.min.js"></script>

    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id="></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'UA-126384684-4');
    </script>
</head>

<body>
    <header>
        <h2>weather-info-ss.com</h2>
    </header>

    <div id="main">
        <div id="form">
            <form method="" action="" onsubmit="return false" >

                <!-- 地点 -->
                <div id="area">
                    <select v-on:change="toggleForm">
                        <option value="area-input-select">地点を選択する</option>
                        <option value="area-input-manual">地点を手動で入力する</option>
                    </select>

                    <div v-if="isActiveSelect">
                        <select id="area_select_prefecture" class="" required>
                            <option value="">都道府県を選択</option>
                        </select>
                        <select id="area_select_city" class="">
                            <option value="">市区町村を選択</option>
                        </select>
                    </div>

                    <div v-if="isActiveManual">
                        <input id="area_manual" type="text" required>
                    </div>
                </div>

                <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
                <script src="<?php echo $this->config->item('base_url') ?>/js/top/prefectureCity.js"></script>
                <script>
                    $(function() {
                    let initPrefVal = '都道府県を選択'; //selectedを付与したい都道府県
                    let initCityVal = '市区町村を選択'; //selectedを付与したい市区町村
                    let valType = 'name';  // 'name' or 'code'

                    // 都道府県リストを作成
                    getPrefectureSelection('#area_select_prefecture', '#area_select_city', '<?php echo $this->config->item('base_url') ?>/json/prefectureCity.json', valType, initPrefVal, initCityVal);

                    // 都道府県選択時に市区町村リストを作成
                    $('#area_select_prefecture').on('change', function() {
                        getCitySelection('#area_select_prefecture', '#area_select_city', '<?php echo $this->config->item('base_url') ?>/json/prefectureCity.json', valType);
                    });
                    });

                </script>

                <!-- 期間 -->
                <div id="term">
                    <label class="font-weight-bold" for="">期間</label>
                    <div>
                        <input id="start_date" type="date" required>
                    </div>～
                    <div>
                        <input id="end_date" type="date" required>
                    </div>
                </div>

                <div>
                    <!-- 落雷有無 -->
                    <div id="thander">
                        <label class="font-weight-bold" for="">落雷有無</label>
                        <select id="thander_select" name="">
                            <!-- <option value="指定なし">
                                指定なし
                            </option> -->
                            <option value="あり">
                                あり
                            </option>
                            <option value="なし">
                                なし
                            </option>
                        </select>
                    </div>

                    <!-- 降水量 -->
                    <div id="precipitation">
                        <label class="font-weight-bold" for="">降水量(mm)</label>
                        <select id="precipitation_select" name="">
                            <option v-for="option in options" v-bind:value="option">
                                {{ option }}
                            </option>
                        </select> mm以上
                    </div>
                </div>
            

                <div>
                    <!-- 最大瞬間風速 -->
                    <div id="wind_speed">
                        <label class="font-weight-bold" for="">最大瞬間風速(m/s)</label>
                        <select id="wind_speed_select" name="">
                            <option v-for="option in options" v-bind:value="option">
                                {{ option }}
                            </option>
                        </select> m/s以上
                    </div>

                    <!-- 風向 -->
                    <div id="wind_direction">
                        <label class="font-weight-bold" for="">風向</label>
                        <select id="wind_direction_select" name="">
                            <option v-for="option in options" v-bind:value="option">
                                {{ option }}
                            </option>
                        </select>
                    </div>
                </div>
                

                <div id="search_button">
                    <button onclick="getData()">検索</button>
                </div>

                <p id="loading"></p>
            </form>
        </div>

        <div id="result-msg" class="font-weight-bold">
            <p id="liden-result-msg"></p>
            <p id="thander-before-liden-result-msg"></p>
            <p id="amedas-result-msg"></p>
        </div>

        <div id="map-parent">
            <div id="map-title" class="font-weight-bold"></div>
            <div id="map"></div>
        </div>
        
        <div id="climate-table"></div>


        <div id="btn-create-img">
            <button id="btn">結果を画像として保存</button>
        </div>
    </div>
    

    

    <script src="<?php echo $this->config->item('base_url') ?>/js/top/index220403.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCe4LhatUzrIC8RXsPyCnDUx9s2p9L3rSQ" async defer></script>

</body>
</html>