<?php
    $APPID = 'ZianWang-hw6leon-PRD-416e557a4-760daf13';

    function getItemByIndex($items, $index){
        $item = $items[$index];
        $item_itemId = $item['itemId'][0];
        $item_title = $item['title'][0];
        $item_galleryURL = $item['galleryURL'][0];
        $item_viewItemURL = $item['viewItemURL'][0];
        $item_postalCode = $item['postalCode'][0];
        $item_shippingInfo = $item['shippingInfo'][0]['shippingServiceCost'][0]['__value__'];
        $item_sellingStatus = $item['sellingStatus'][0]['currentPrice'][0];
        $item_currencyId = $item_sellingStatus['@currencyId'];
        $item_value = $item_sellingStatus['__value__'];
        $item_condition = $item['condition'][0]['conditionDisplayName'][0];
        $return_item = array();
        $return_item['itemId'] = $item_itemId;
        $return_item['title'] = $item_title;
        $return_item['galleryURL'] = $item_galleryURL;
        $return_item['viewItemURL'] = $item_viewItemURL;
        if(isset($item_postalCode)){
            $return_item['postalCode'] = $item_postalCode;
        }else{
            $return_item['postalCode'] = 'N/A';
        }
        if(!isset($item_shippingInfo)){
            $return_item['shippingInfo'] = "N/A";
        }else if($item_shippingInfo==0){
            $return_item['shippingInfo'] = "Free Shipping";
        }else{
            $return_item['shippingInfo'] = '$'.$item_shippingInfo;
        }
        $return_item['value'] = '$'.$item_value;
        if(isset($item_condition)){
            $return_item['condition'] = $item_condition;
        }else{
            $return_item['condition'] = 'N/A';
        }

        return $return_item;
    }
    function getSimilarByIndex($items, $index){
        $item = $items[$index];

        $itemId = $item['itemId'];
        $title = $item['title'];
        $imageURL = $item['imageURL'];
        $buyItNowPrice = $item['buyItNowPrice']['__value__'];
        $currentPrice = $item['currentPrice']['__value__'];
        if(!isset($currentPrice)){
            $item_value = $buyItNowPrice;
        }else{
            $item_value = $currentPrice;
        }

        $similar_item = array();
        $similar_item['itemId'] = $itemId;
        $similar_item['title'] = $title;
        $similar_item['imageURL'] = $imageURL;
        $similar_item['value'] = '$'.$item_value;
        return $similar_item;
    }
    function getItemDetail($item_detail){
        $description = $item_detail['Description'];
        $location = $item_detail['Location'];
        $postcode = $item_detail['PostalCode'];
        $picture = $item_detail['PictureURL'][0];
        $seller = $item_detail['Seller']['UserID'];
        $price = $item_detail['CurrentPrice'];
        $value = $price['Value'];
        $currency = $price['CurrencyID'];
        $title = $item_detail['Title'];
        $subtitle = $item_detail['Subtitle'];
        $specifics = $item_detail['ItemSpecifics']['NameValueList'];
        $return_policy = $item_detail['ReturnPolicy'];
        $return_within = $return_policy['ReturnsWithin'];
        $return_accepted = $return_policy['ReturnsAccepted'];
        $item_id = $item_detail['ItemID'];

        $item_detail = array();
        if(!isset($description)){
            $description="";
        }
        if(!isset($postcode)){
            $postcode="undefined";
        }
        $item_detail['description'] = $description;

        $item_detail['location'] = $location.", ".$postcode;
        $item_detail['picture'] = $picture;
        $item_detail['seller'] = $seller;
        $item_detail['price'] = $value." ".$currency;
        $item_detail['title'] = $title;
        $item_detail['subtitle'] = $subtitle;
        $item_detail['itemId'] = $item_id;
        $sp = array();
        if(isset($specifics)){
            foreach ($specifics as $specific) {
                $sp[$specific['Name']] = $specific['Value'][0];
            }
        }
        $item_detail['sp'] = $sp;
        if ($return_accepted=="ReturnsNotAccepted"){
            $item_detail['policy'] = "Returns Not Accepted";
        }else{
            $item_detail['policy'] = $return_accepted." within ".$return_within;
        }

        return $item_detail;
    }
    function test_argument(){
        var_dump($_POST);
        var_dump(isset($_POST['condition']));
        $item_filter_index = 0;
        if (isset($_POST['condition'])){
            $item_value_index = 0;
            $url = $url.'&itemFilter('.$item_filter_index.').name=Condition';
            foreach ($_POST['condition'] as $key => $value){
                $url = $url.'&itemFilter('.$item_filter_index.').value('.$item_value_index.')='.$value;
                $item_value_index++;
            }
            $item_filter_index++;
        }
        echo $url;
    }
    function buildSearchUrl(){
        global $APPID;
        $url_base = 'http://svcs.ebay.com/services/search/FindingService/v1';
        $operation = 'findItemsAdvanced';
        $request_number = 20;
        $url=$url_base.'?OPERATION-NAME='.$operation.'&SERVICE-VERSION=1.0.0&SECURITY-APPNAME='.$APPID.'&REST-PAYLOAD&RESPONSE-DATA-FORMAT=JSON&paginationInput.entriesPerPage='.$request_number;
        $url = $url.'&keywords='.urlencode($_POST['keyword']);
        if (!($_POST['category']=='0')){
            $url = $url.'&categoryId='.$_POST['category'];
        }
        if ($_POST['location'][0]=="here"){
            $zip = $_POST['local_ip'];
        }else{
            $zip = $_POST['zip'];
        }
        $item_filter_index = 0;

        if(isset($_POST['nearby'])){
            $url = $url.'&buyerPostalCode='.$zip;
            $url = $url.'&itemFilter('.$item_filter_index.').name=MaxDistance&itemFilter('.$item_filter_index.').value='.$_POST['mile'];
            $item_filter_index++;
        }

        $url = $url.'&itemFilter('.$item_filter_index.').name=HideDuplicateItems&itemFilter('.$item_filter_index.').value=true';
        $item_filter_index++;

        if (isset($_POST['condition'])){
            $item_value_index = 0;
            $url = $url.'&itemFilter('.$item_filter_index.').name=Condition';
            foreach ($_POST['condition'] as $key => $value){
                $url = $url.'&itemFilter('.$item_filter_index.').value('.$item_value_index.')='.$value;
                $item_value_index++;
            }
            $item_filter_index++;
        }
        if (isset($_POST['shipping'])){
            foreach ($_POST['shipping'] as $key => $value){
                $url = $url.'&itemFilter('.$item_filter_index.').name='.$value;
                $url = $url.'&itemFilter('.$item_filter_index.').value=true';
                $item_filter_index++;
            }
        }


        return $url;
    }
    function buildDetailUrl($item_id){
        global $APPID;
        $url_base = 'http://open.api.ebay.com/shopping';
        $operation = 'GetSingleItem';
        $url = $url_base.'?callname='.$operation.'&responseencoding=JSON&appid='.$APPID;
        $url = $url.'&siteid=0&version=967&ItemID='.$item_id;
        $url = $url.'&IncludeSelector=Description,Details,ItemSpecifics';
        return $url;
    }
    function buildSimilarUrl($item_id){
        global $APPID;
        $url_base = 'http://svcs.ebay.com/MerchandisingService';
        $operation = 'getSimilarItems';
        $request_number = 8;
        $url = $url_base.'?OPERATION-NAME='.$operation.'&SERVICE-NAME=MerchandisingService&SERVICE-VERSION=1.1.0&CONSUMER-ID='.$APPID;
        $url = $url.'&RESPONSE-DATA-FORMAT=JSON&REST-PAYLOAD&itemId='.$item_id;
        $url = $url.'&maxResults='.$request_number;
        return $url;
    }
    if ($_POST['ask_json']=='search_list') {
        $url = buildSearchUrl();
        $contents = file_get_contents($url);
        $d_contents = json_decode($contents, true);
        $searchResult = $d_contents['findItemsAdvancedResponse'][0]['searchResult'][0];
        $ack_result = $d_contents['findItemsAdvancedResponse'][0]['ack'][0];
        if($ack_result=='Failure'){
            $error_message = $d_contents['findItemsAdvancedResponse'][0]['errorMessage'][0]['error'][0]['message'][0];
            echo '[false,"'.$error_message.'"]';
            return;
        }
        if(!isset($searchResult)){
            $error_message = 'Search is invalid';
            echo '[false,"'.$error_message.'"]';
        }
        $items = $searchResult['item'];
        $count = $searchResult['@count'];
        $return_items = array();
        for ($i=0; $i < $count; $i++) {
            $return_item = getItemByIndex($items, $i);
            array_push($return_items, $return_item);
        }
        echo json_encode($return_items);
    }else if($_POST['ask_json']=='item_detail'){
        $url = buildDetailUrl($_POST['itemId']);
        $contents = file_get_contents($url);
        $d_contents = json_decode($contents, true);
        $itemResult = $d_contents['Item'];
        $ack_result = $d_contents['Ack'];
        if($ack_result=='Failure'){
            $error_message = $d_contents['Errors'][0]['ShortMessage'];
            echo '[false,"'.$error_message.'"]';
            return;
        }
        if(!isset($itemResult)){
            $error_message = 'Item Details are not available.';
            echo '[false,"'.$error_message.'"]';
        }
        $item_detail = getItemDetail($itemResult);
        echo json_encode($item_detail);
    }else if($_POST['ask_json']=='item_similar'){
        $url = buildSimilarUrl($_POST['itemId']);
        // echo $url;
        $contents = file_get_contents($url);
        $d_contents = json_decode($contents, true);
        $similarResult = $d_contents['getSimilarItemsResponse']['itemRecommendations']['item'];
        $count = count($similarResult);
        $similar_items = array();
        for ($i=0; $i < $count; $i++) {
            $similar_item = getSimilarByIndex($similarResult, $i);
            array_push($similar_items, $similar_item);
        }
        echo json_encode($similar_items);
    }else if(isset($_GET['description'])){
        $url = buildDetailUrl($_GET['description']);
        $contents = file_get_contents($url);
        $d_contents = json_decode($contents, true);
        $description = $d_contents['Item']['Description'];
        if(!isset($description)){
            $description="";
        }
        echo $description;
    }else{
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Homework6</title>
    <style>
        body{
          font-family: Times;
        }
        #div-form{
            margin: 0 auto;
            width: 535px;
            background-color: #FAF9FA;
            border-color: #C8C7C8;
            border-style: solid;
        }
        #form-title{
            font-size: 30px;
            text-align: center;
            /* font-weight: bold; */
            font-style: italic;
        }

        label{
            font-weight: bold;
            vertical-align: top;
        }

        hr{
            margin: 10px;
            border: none;
            height: 1px;
            background-color: #999999;
        }

        #div-input{
            margin-left: 20px;
            font-size: 14px;
        }

        input{
            margin-bottom:15px;
            vertical-align:top;
            font-size: 11px;
            border: solid;
            border-width: 1px;
            border-color: #CCCCCC;
        }

        select{
            margin-bottom:15px;
            vertical-align:top;
            font-size: 11px;
            border-width: 1px;
        }

        input[type="checkbox"]{
            margin-top: 2px;
            height:12px;
            vertical-align:top;
        }

        input[type="radio"]{
            margin-top: 0px;
            height:18px;
            vertical-align:top;
        }

        input[name="condition[]"]{
            margin-left: 18px;
        }

        input[name="shipping[]"]{
            margin-left: 39px;
        }

        input[name="nearby"]{
            margin-left: 1px;
            margin-right: 2px;
        }

        input[name="nearby"]{
            margin-left: 1px;
            margin-right: 2px;
        }

        #keyword{
            margin-top: 0px;
            width: 110px;
        }


        #mile{
            margin-top: 0px;
            margin-left: 25px;
            height: 12px;
            width: 50px;
        }

        #radio-zip{
            margin-top: -12px;
        }

        #zip{
            margin-top: -14px;
            width: 110px;
        }

        #div-submit{
            width:150px;
            margin: 0px auto 20px;
            font-family: Arial;
        }

        #div-submit button{
            font-size: 12px;
            /* font-weight: bold; */
            background-color: #FFFFFF;
            border-radius: 2px;
            border: solid;
            border-color: #DDDDDD;
            border-width: 1px;
        }
        .container{
            position: relative;
            opacity: 0.5;

        }
        .content {
            position: absolute;
            left:0;
            bottom:0;
            background-color: rgba(0,0,0,0);
            z-index: 1;
            width: 300px;
            height: 50px;
        }

        table, th, td{
            border: 2px solid #CCCCCC;
        }

        table{
            border-collapse: collapse;
        }

        #div-table{
            margin-top: 20px;
            padding: 0 40px;
        }

        #div-table td {
            padding: 0;
        }

        .error-msg{
            margin: 30px auto 0 auto;
            width: 650px;
            background-color: #EEEEEE;
            border: 2px solid #DDDDDD;
            text-align: center;
            font-size: 16px;
        }

        a {
            text-decoration: none;
            color: black;
        }

        a:hover{
            color: #909090;
        }

        #div-detail td{
            padding: 0 0 0 10px;
            white-space: nowrap;
        }

        #item_detail_title{
            font-weight: bold;
            text-align: center;
            font-size: 30px;
        }

        table img{
            margin-bottom: -4px;
        }

        #div-detail #no_detail{
            padding: 0;
            min-width: 178px;
        }

        #grey_bg{
            background-color: #CCCCCC;
        }

        #iframe{
            width: 100%;
            display: block;
        }

        #seller_message{
            text-align: center;
            margin-top: 30px;
            color: #909090;
        }

        #seller_message img, #similar_item img{
            margin-top: 10px;
            width: 50px;
            height: 25px;
        }

        iframe{
            padding-top: 30px;
        }

        #similar_item{
            margin-top: 20px;
            text-align: center;
            color: #909090;
        }

        #similar_block{
            width: 800px;
            border: 2px solid #CCCCCC;
            margin: 10px auto 30px auto;
            overflow: auto;
            white-space:nowrap;
        }

        .similar_img{
            max-height:150px;
            max-width: 240px;
        }

        .similar_square{
            text-align: center;
            display: inline-block;
            width: 240px;
            white-space:normal;
        }

        #no_similar{
            margin: 10px;
            border: 1px solid #DDDDDD;
            text-align: center;
            width: 800px;
            display: inline-block;
        }


    </style>
    <script>

        window.onload = function(){
            var ip_url = "http://ip-api.com/json";
            xmlhttp=new XMLHttpRequest();
            xmlhttp.open("GET",ip_url,false);
            xmlhttp.send();
            ip_json = JSON.parse(xmlhttp.responseText);
            local_ip=ip_json.zip;
            if(/^\d{5}$/.test(local_ip))
                document.getElementById("submit-search").removeAttribute("disabled");
        }

        block_container=null;
        block_content=null;
        mile=null;
        similar_id=null;
        function switch_nearby(nearby){
            if(!block_container)
                block_container = document.getElementById("nearby-container");
            if(!block_content)
                block_content = document.getElementById("nearby-content");
            if(!mile)
                mile = document.getElementById("mile");

            var ischecked = nearby.checked;
            if(ischecked){
                block_container.style.opacity=1;
                block_container.removeChild(block_content);
            }else{
                block_container.style.opacity=0.5;
                block_container.insertBefore(block_content,mile);
                clear_nearby_search();
            }
        }

        function open_zip_required(){
            document.getElementById("zip").required=true;
        }

        function close_zip_required(){
            document.getElementById("zip").required=false;
            document.getElementById("zip").value="";
        }

        function zip_onclick(zip_input){
            if(document.getElementById("radio-here").checked){
                zip_input.blur();
            }
        }

        function clear_nearby_search(){
            document.getElementById("radio-here").checked=true;
            document.getElementById("zip").required=false;
            document.getElementById("zip").value="";
            document.getElementById("mile").value="";
        }

        function check_value(fd){
            var zip_code = fd.zip.value;
            var mile = fd.mile.value;
            var mile_valid=(mile=="")||(!fd.nearby.checked)||(/^[1-9]\d*$/.test(mile));
            if(!mile_valid){
                clearDiv();
                document.getElementById("div-error").innerHTML="<div class='error-msg'>Mile is invalid</div>"
                return false;
            }
            var radio_zip = document.getElementById("radio-zip");

            if(radio_zip.checked && !(/^\d{5}$/.test(zip_code))){
                clearDiv();
                document.getElementById("div-error").innerHTML="<div class='error-msg'>Zipcode is invalid</div>"
                return false;
            }
            return true;
        }

        function clearDiv(){
            document.getElementById("div-error").innerHTML="";
            document.getElementById("div-table").innerHTML="";
            document.getElementById("div-detail").innerHTML="";
        }

        function clearSearch(){
            document.getElementById("keyword").value="";
            document.getElementById("category").options.selectedIndex=0
            document.getElementById("checkbox-new").checked=false;
            document.getElementById("checkbox-used").checked=false;
            document.getElementById("checkbox-unsp").checked=false;
            document.getElementById("checkbox-local").checked=false;
            document.getElementById("checkbox-free").checked=false;
            var ischecked = document.getElementById("checkbox-nearby").checked;
            if(ischecked){
                document.getElementById("checkbox-nearby").checked=false;
                block_container.style.opacity=0.5;
                block_container.insertBefore(block_content,mile);
                clear_nearby_search();
            }
        }

        function clearAll(){
            clearSearch();
            clearDiv();
        }

        function searchList(fd){
            if(fd.checkValidity()){
                if(check_value(fd)){
                    clearDiv();
                    url = "<?php echo $_SERVER['PHP_SELF']; ?>";
                    xmlhttp=new XMLHttpRequest();
                    xmlhttp.open("POST",url,false);
                    form = new FormData(fd);
                    if ((fd.mile.value=="")&&(fd.nearby.checked))
                        form.append("mile","10");
                    form.append("local_ip",local_ip);
                    form.append("ask_json","search_list");
                    xmlhttp.send(form);
                    jsonObj = JSON.parse(xmlhttp.responseText);
                    if(jsonObj.length==2 && jsonObj[0]==false){
                        var error_msg = jsonObj[1];
                        if(error_msg=="Invalid keyword."){
                            error_msg="Keyword is invalid";
                        }
                        clearDiv();
                        document.getElementById("div-error").innerHTML="<div class='error-msg'>"+error_msg+"</div>";
                        return;
                    }
                    if(jsonObj.length==0){
                        clearDiv();
                        document.getElementById("div-error").innerHTML="<div class='error-msg'>No Records has been found</div>";
                        return;
                    }
                    //console.log(jsonObj);
                    generateTable(jsonObj)
                    document.getElementById("div-table").innerHTML=table;
                }
            }
        }

        function item_detail(itemId){
            clearDiv();
            url = "<?php echo $_SERVER['PHP_SELF']; ?>";
            xmlhttp=new XMLHttpRequest();
            xmlhttp.open("POST",url,false);
            form = new FormData();
            form.append("itemId",itemId);
            form.append("ask_json","item_detail");
            xmlhttp.send(form);
            jsonObj = JSON.parse(xmlhttp.responseText);
            if(jsonObj.length==2 && jsonObj[0]==false){
                var error_msg = jsonObj[1];
                clearDiv();
                document.getElementById("div-error").innerHTML="<div class='error-msg'>"+error_msg+"</div>";
                return;
            }
            similar_id = itemId;
            generateDetail(jsonObj);
            document.getElementById("div-detail").innerHTML=detail;
        }



        function generateTable(jsonObj){
            table = "";
            table += "<table style='width:100%'>";
            table += "<tr>";
            var headers = ["Index","Photo","Name","Price","Zip code","Condition","Shipping Option"];
            for (var i = 0; i < headers.length; i++) {
                table += "<th>" + headers[i] + "</th>";
            }
            table += "</tr>";
            for (var i = 0; i < jsonObj.length; i++) {
                table += "<tr>";
                var item = jsonObj[i];
                table += "<td style='min-width: 40px; width: 40px; max-width: 40px;'>"+(i+1)+"</td>";
                if(item.galleryURL)
                    table += "<td style='min-width: 70px; width: 70px; max-width: 70px;'><img src='"+item.galleryURL+"' style='max-width: 70px;'></td>";
                else
                    table += "<td style='min-width: 70px; width: 70px; max-width: 70px;'></td>";
                table += "<td><a href='javascript:void(0);' onclick='item_detail("+item.itemId+")'>"+item.title+"</a></td>";
                table += "<td style='min-width: 57px; width: 57px; max-width: 57px;'>"+item.value+"</td>";
                table += "<td style='min-width: 63px; width: 63px; max-width: 63px;'>"+item.postalCode+"</td>";
                table += "<td style='min-width: 180px;width: 180px;max-width: 180px;'>"+item.condition+"</td>";
                table += "<td style='min-width: 123px;width: 123px;max-width: 123px;'>"+item.shippingInfo+"</td>";
                table += "</tr>";
            }
            table += "</table>";

        }

        function generateDetail(jsonObj){
            var headers = ["Photo","Title","Subtitle","Price","Location","Seller","Return Policy(US)"];
            detail = "";
            detail += "<div id='item_detail_title'>Item Details</div>";
            detail += "<table style='margin:0 auto;'>";
            if(jsonObj.picture)
                detail += "<tr><td><b>"+headers[0]+"</b></td><td><img src='"+jsonObj.picture+"' style='height:200px;'></td></tr>";
            detail += "<tr><td><b>"+headers[1]+"</b></td><td>"+jsonObj.title+"</td></tr>";
            if(jsonObj.subtitle!=null)
                detail += "<tr><td><b>"+headers[2]+"</b></td><td>"+jsonObj.subtitle+"</td></tr>";
            detail += "<tr><td><b>"+headers[3]+"</b></td><td>"+jsonObj.price+"</td></tr>";
            detail += "<tr><td><b>"+headers[4]+"</b></td><td>"+jsonObj.location+"</td></tr>";
            detail += "<tr><td><b>"+headers[5]+"</b></td><td>"+jsonObj.seller+"</td></tr>";
            detail += "<tr><td style='min-width: 140px;'><b>"+headers[6]+"</b></td><td>"+jsonObj.policy+"</td></tr>";
            var specifics = jsonObj.sp;
            var keys = Object.keys(specifics)
            if(specifics.length==0){
                detail += "<tr><td id='no_detail'><b>No Detail Info from Seller</b></td><td id='grey_bg'></td></tr>";
            }
            for (var i = 0; i < keys.length; i++) {
                detail += "<tr><td><b>"+keys[i]+"</b></td><td>"+specifics[keys[i]]+"</td></tr>";
            }
            detail += "</table>";
            detail += "<div id='seller_message'><div id='seller_msg'>click to show seller message</div><img id='img_sm' src='http://csci571.com/hw/hw6/images/arrow_down.png' onclick='add_iframe(this)'></div><div id='div-iframe' style='padding:0 40px;'></div>"
            detail += "<div id='similar_item'><div id='s_item'>click to show similar items</div><img id='img_si' src='http://csci571.com/hw/hw6/images/arrow_down.png' onclick='add_similar(this)'></div><div id='div-similar'></div>"
        }

        function add_iframe(img){
            if(img.src.endsWith("arrow_down.png")){
                document.getElementById("img_si").src = "http://csci571.com/hw/hw6/images/arrow_down.png";
                document.getElementById("s_item").innerHTML="click to show similar items";
                document.getElementById("div-similar").innerHTML="";

                img.src = "http://csci571.com/hw/hw6/images/arrow_up.png";
                document.getElementById("seller_msg").innerHTML="click to hide seller message";
                if(!(!jsonObj.description||jsonObj.description=="")){
                    document.getElementById("div-iframe").innerHTML="<iframe src='<?php echo $_SERVER['PHP_SELF']; ?>?description="+similar_id+"' height='10' frameborder='0' scrolling='no' id='iframe' onload='this.height = this.contentWindow.document.body.scrollHeight;'></iframe>";
                }else{
                    document.getElementById("div-iframe").innerHTML="<div class='error-msg' style='background-color:#DDDDDD'><b>No Seller Message found.</b></div>"
                }
            }
            else{
                img.src = "http://csci571.com/hw/hw6/images/arrow_down.png";
                document.getElementById("seller_msg").innerHTML="click to show seller message";
                document.getElementById("div-iframe").innerHTML="";
            }
        }


        function add_similar(img){
            if(img.src.endsWith("arrow_down.png")){
                document.getElementById("img_sm").src = "http://csci571.com/hw/hw6/images/arrow_down.png";
                document.getElementById("seller_msg").innerHTML="click to show seller message";
                document.getElementById("div-iframe").innerHTML="";

                img.src = "http://csci571.com/hw/hw6/images/arrow_up.png";
                document.getElementById("s_item").innerHTML="click to hide similar items";

                generateSimilar(similar_id);
                document.getElementById("div-similar").innerHTML=similar;
            }
            else{
                img.src = "http://csci571.com/hw/hw6/images/arrow_down.png";
                document.getElementById("s_item").innerHTML="click to show similar items";
                document.getElementById("div-similar").innerHTML="";
            }
        }

        function generateSimilar(itemId){
            url = "<?php echo $_SERVER['PHP_SELF']; ?>";
            xmlhttp=new XMLHttpRequest();
            xmlhttp.open("POST",url,false);
            form = new FormData();
            form.append("itemId",itemId);
            form.append("ask_json","item_similar");
            xmlhttp.send(form);
            jsonObj3 = JSON.parse(xmlhttp.responseText);

            similar = "";
            similar += "<div id='similar_block'>"
            if(jsonObj3.length==0){
                similar += "<div id='no_similar'><b style='color:black;'>No Similar Item found.</b></div>"
            }else{
                for (var i = 0; i < jsonObj3.length; i++) {
                    generateSimilarItem(jsonObj3[i]);
                }
            }

            similar += "</div>"
        }

        function generateSimilarItem(item){
            similar += "<div class='similar_square'><div>"
            similar += "<img src='"+item.imageURL+"' class='similar_img'><br/>"
            similar += "<a href='javascript:void(0);' onclick='item_detail("+item.itemId+")'>"+item.title+"</a>"
            similar += "<div style='color:black;margin-top:10px;'><b>"+item.value+"</b></div>"
            similar += "</div></div>"
        }


    </script>
</head>
<body>
    <div id="div-form">
        <form action="" method="POST" onsubmit="return false;">
            <div id="form-title">Product Search</div>
                <hr>
                <div id="div-input">
                <label for="keyword" required>Keyword</label>
                <input type="text" name="keyword" id="keyword" required>
                <br>
                <label for="category">Category</label>
                <select name="category" id="category" checked>
                    <option value="0">All Categories</option>
                    <!-- <option value="" disabled>- - - - - - - - - - - - - - - - - - - - - - - - -</option> -->
                    <option value="550">Art</option>
                    <option value="2984">Baby</option>
                    <option value="267">Books</option>
                    <option value="11450">Clothing, Shoes &amp; Accessories</option>
                    <option value="58058">Computers/Tablets &amp; Networking</option>
                    <option value="26395">Health &amp; Beauty</option>
                    <option value="11233">Music</option>
                    <option value="1249">VideoGames &amp; Consoles</option>
                    </optgroup>
                </select>
                <br>
                <label>Condition</label>
                <input type="checkbox" name="condition[]" id="checkbox-new" value="New">New
                <input type="checkbox" name="condition[]" id="checkbox-used" value="Used">Used
                <input type="checkbox" name="condition[]" id="checkbox-unsp" value="Unspecified">Unspecified
                <br>
                <label for="shipping">Shipping Options</label>
                <input type="checkbox" name="shipping[]" id="checkbox-local" value="LocalPickupOnly">Local Pickup
                <input type="checkbox" name="shipping[]" id="checkbox-free" value="FreeShippingOnly">Free Shipping

                <br>
                <input type="checkbox" name="nearby" id="checkbox-nearby" onclick="switch_nearby(this)">
                <label for="nearby">Enable Nearby Search</label>
                <div id="nearby-container" class="container" style="display: inline;">
                    <div id="nearby-content" class="content" style="display: inline;"></div>
                    <input type="text" name="mile" id="mile" placeholder="10">
                    <label for="mile">miles from</label>
                    <div style="display:inline-block;">
                        <input type="radio" name="location[]" value="here" id="radio-here" checked onclick="close_zip_required()">Here
                        <div>
                            <input type="radio" name="location[]" value="zip" id="radio-zip" onclick="open_zip_required()">
                            <input type="text" name="zip" id="zip" placeholder="zip code" onclick="zip_onclick(this)">
                        </div>
                    </div>
                    <br>
                </div>

            </div>
            <div id="div-submit">
                <button id="submit-search" type="submit" onclick="searchList(this.form);" disabled>Search</button>
                <button type="button" onclick="clearAll();">Clear</button>
            </div>
        </form>
    </div>
    <div id="div-table">
    </div>
    <div id="div-error">
    </div>
    <div id="div-detail">
    </div>
</body>
</html>
<?php }?>
