<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>	
		<title>TRAM: Transportation Research at McGill</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">		
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
		<link rel="stylesheet" href="/reset.css" media="screen,projection" type="text/css" />		
        <link rel="stylesheet" type="text/css" media="screen and (min-device-width:1025px)" href="/style-large-screen.css">
        <link rel="stylesheet" type="text/css" media="screen and (max-device-width:1024px)" href="/style-mobile.css">
		<link rel="shortcut icon" href="/images/tramIcon.ico">
		
		<script src="http://code.jquery.com/jquery-1.11.1.min.js"></script>
        <script src="http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>		
		
		<script>
			var globalSlideShowState = "play2";			
			var globalTimer = -1;			
			
            // for mobile
            var imageNameList = [];
            var currentImageIndex = 0;
            var fullScreen = false;
            var imageLoop = false;
            
            function isMobile()
            {   var agent = navigator.userAgent.toLowerCase();	
                return agent.indexOf("iphone") != -1 || agent.indexOf("ipad") != -1 || agent.indexOf("android") != -1;
            }
                           			
			$( document ).ready(function() {
                // loads the menu and footer from topbar.html and footer.html
				$('#topbarContent').load('/topbar.html');
				$('#footerContent').load('/footer.html');  
                               
                if (isMobile())
                {   fixViewportUnits(false);
                    $(window).on("orientationchange",changeOrientation); 
                    document.getElementById("fullScreenSlideShowStarter").addEventListener("click", function (e) {                         
                        document.getElementById("fullScreenSlideShowWrapper").style.display = "block";
                        document.getElementById("fullScreenSlideShowStarter").style.display = "none";                        
                        goFullScreen();                                                
                        startSlideShow(0);
                    }, false);                                       
                }
                loadImages();
				startSlideShow(3000);
			}); 

            function changeOrientation()
            {   setTimeout(function(){ fixViewportUnits(true); }, 500);
            }
            
            function fixViewportUnits(reorient)
            {   var vw = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
                var vh = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);                        
                //alert('!');                
                
                var box = document.getElementById('fullScreenSlideShowStarter');
                box.style.height = (box.offsetWidth * 0.75) + "px";
                document.getElementById('fullScreenSlideShowStarterOverlay').style.paddingTop = (((box.offsetWidth * 0.75) - (4*5)) / 2) + "px";
                
                //document.getElementById('fullScreenSlideShowStarter').style.width = (vw - (18 * 5)) + "px";
                //document.getElementById('fullScreenSlideShowStarter').style.height = ((vw - (18 * 5)) * 0.75) + "px";
                //document.getElementById('fullScreenSlideShowStarterOverlay').style.paddingTop = (((vw - (18*5)) * 0.75 - (4*5)) / 2 ) + "px";
                
            }            

			function slideControlOver(control)
			{	document.getElementById(control).src = "/images/slideshow_black_" + control + ".png";
			}
			
			function slideControlOut(control)
			{	var newColour = "";
				if (globalSlideShowState == control)
					newColour = "orange";
				else
					newColour = "blue";				
				
				document.getElementById(control).src = "/images/slideshow_" + newColour + "_" + control + ".png";
			}
			
			function slideControlClick(control)
			{	clearInterval(globalTimer);	
			
				if (control == "play1")
					startSlideShow(5000)
				else if (control == "play2")
					startSlideShow(3000)
				else if (control == "play3")
					startSlideShow(1500)
				
				// update buttons
				document.getElementById(globalSlideShowState).src = "/images/slideshow_blue_" + globalSlideShowState + ".png";
				globalSlideShowState = control;
				document.getElementById(globalSlideShowState).src = "/images/slideshow_orange_" + globalSlideShowState + ".png";
			}			
            
            function startSlideShow(speed)
			{	
                if (isMobile())
                {   				alert('a');
                    if (fullScreen)
                    {   
                        var images = document.getElementsByClassName('fullScreenSlideShowImage');
                        if (images.length > 0)  // already been here; just re-calculate image dimensions
                        {   for (var i = 0; i < images.length; i++)                            
                            {   calculateDimensions(images[i]);                                                        
                                images[i].offsetHeight;
                            }
                            return;
                        }   
                        currentImageIndex = 0;
                        for (var i = 0; i < 2; i++)
                        {   //alert(i);
                            loadImage(imageNameList[i], i, 2-i);
                        }                         
                        var currentImage = document.getElementById('fullScreenSlideShowImage' + currentImageIndex); 
                        currentImage.style.left = "0vw";
                        currentImage.style.right = "0vw";                        
                        
                        //alert('a');
                        

                        $(window).on("orientationchange",function(event)
                        {                              
                            if (fullScreen == false)
                                return;
                            
                            document.getElementById('fullScreenSlideShow').style.display = "none";
                            var images = document.getElementsByClassName('fullScreenSlideShowImage'); 
                            for (var i = 0; i < images.length; i++)                            
                                images[i].style.display = "none";
                            
                            setTimeout(function()
                            {   var images = document.getElementsByClassName('fullScreenSlideShowImage'); 
                                for (var i = 0; i < images.length; i++)                            
                                {   calculateDimensions(images[i]);                                                        
                                    images[i].offsetHeight;
                                }
                                document.getElementById('fullScreenSlideShow').style.display = "block";                                
                                for (var i = 0; i < images.length; i++)                            
                                    images[i].style.display = "block";
                                    
                            }, 200);
                            
                            
                            
                            
                            
                            //alert('orientationchange - done');
                            //document.getElementById('fullScreenSlideShow').style.display = "block";
                        });                        
                        
                        $("#fullScreenSlideShow").on("swipeleft",function()
                        {                           
                            var currentImage = document.getElementById('fullScreenSlideShowImage' + currentImageIndex); 
                            currentImage.style.left = '-100vw';
                            currentImage.style.right = "100vw";
                            
                            var nextImage = document.getElementById('fullScreenSlideShowImage' + ((currentImageIndex+1)%imageNameList.length));
                            nextImage.style.left = '0vw';
                            nextImage.style.right = "0vw";
                                                        
                            if (imageLoop == false && currentImageIndex+2 < imageNameList.length)
                                loadImage(imageNameList[currentImageIndex+2], currentImageIndex+2, 1);
                            else
                            {   var nextNextImage = document.getElementById('fullScreenSlideShowImage' + ((currentImageIndex+2)%imageNameList.length) );
                                calculateDimensions(nextNextImage, 'nextnext');
                                nextNextImage.style.left = "100vw";
                                nextNextImage.style.right = "-100vw";
                            }
                            
                            currentImageIndex++;
                            if (currentImageIndex == imageNameList.length)
                            {   imageLoop = true;
                                currentImageIndex = currentImageIndex % imageNameList.length;
                            }
                        });
                        
                        $("#fullScreenSlideShow").on("swiperight",function()
                        {
                            var images = document.getElementsByClassName('fullScreenSlideShowImage');
                            if (currentImageIndex == 0 && images.length < imageNameList.length)                                 
                                return;  // not all images have been loaded; can't cycle around to end
                            
                            var currentImage = document.getElementById('fullScreenSlideShowImage' + currentImageIndex); 
                            currentImage.style.left = '100vw';
                            currentImage.style.right = "-100vw";
                            
                            var prevImageIndex = currentImageIndex - 1;
                            if (prevImageIndex == -1)
                                prevImageIndex = imageNameList.length - 1;
                                
                            var prevImage = document.getElementById('fullScreenSlideShowImage' + (prevImageIndex));
                            prevImage.style.left = '0vw';
                            prevImage.style.right = "0vw";
                            
                            currentImageIndex = prevImageIndex;
                            
                            // prep the next image
                            if (prevImageIndex == 0 && images.length < imageNameList.length)                                 
                                return;  // not all images have been loaded; can't cycle around to end (on next swipe right)
                                
                            var prevPrevImageIndex = prevImageIndex - 1;
                            if (prevPrevImageIndex == -1)
                                prevPrevImageIndex = imageNameList.length - 1;
                                
                            var prevPrevImage = document.getElementById('fullScreenSlideShowImage' + (prevPrevImageIndex));
                            prevPrevImage.style.left = '-100vw';
                            prevPrevImage.style.right = "100vw";     
                                                        
                        });
                    }
                }
                else
                {
                    $(function()
                    {	$('.slideshow > :gt(0)').hide();
                        globalTimer = setInterval(function()					
                        {
                            $('.slideshow > :first-child').fadeOut().next().fadeIn().end().appendTo('.slideshow'); 
                        }, speed);												
                    });					
                }
			}	                        
			
			function appendImage(imageName)
			{	if (isMobile())
                {   imageNameList.push(imageName);
                }
                else
                {
                    var node = document.createElement('img');
                    node.setAttribute('class', 'fullScreenSlideShowImage');
                    node.setAttribute('src', imageName);				
                    document.getElementById('fullScreenSlideShow').appendChild(node);		
                }
			}
            
            function loadImage(imageName, id, zIndex)
            {   //alert('loading');
                var node = document.createElement('img');
                node.setAttribute('id', 'fullScreenSlideShowImage' + id);
                node.setAttribute('class', 'fullScreenSlideShowImage');                
                node.setAttribute('style', 'display: none; z-index:' + zIndex + '; left: 100vw; right: -100vw;');				                
                node.setAttribute('src', imageName);
                node.addEventListener("load", function(e) 
                {   calculateDimensions(node, 'initial');
                    node.style.display = "block";
                }, false);
                document.getElementById('fullScreenSlideShow').appendChild(node);                                             
            }
            
            function calculateDimensions(image, tag)
            {                   
                var screenRatio = window.outerWidth / window.outerHeight;               
                var imageRatio = image.width / image.height;
                
                if (imageRatio < screenRatio)
                {   image.style.height = '100vh';
                    image.style.width = (100*imageRatio) + 'vh';
                    image.style.top = '0vh';
                }
                else
                {   image.style.width = '100vw';
                    image.style.height = (100/imageRatio) + 'vw';
                    image.style.top = 'calc( (100vh - (100vw / ' + imageRatio + ')) / 2)';
                }                                            
            }
            
            function goFullScreen() 
            {
                if (!document.fullscreenElement &&    
                    !document.mozFullScreenElement && !document.webkitFullscreenElement && !document.msFullscreenElement ) 
                {   if (document.documentElement.requestFullscreen) 
                    {    document.documentElement.requestFullscreen();
                    } 
                    else if (document.documentElement.msRequestFullscreen) 
                    {    document.documentElement.msRequestFullscreen();
                    } 
                    else if (document.documentElement.mozRequestFullScreen) 
                    {    document.documentElement.mozRequestFullScreen();
                    } 
                    else if (document.documentElement.webkitRequestFullscreen) 
                    {    document.documentElement.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
                    }
                    fullScreen = true;
                }                 
            }
            
            function closeFullScreen()
            {   
                if (document.exitFullscreen) 
                   document.exitFullscreen();                
                else if (document.msExitFullscreen) 
                   document.msExitFullscreen();
                else if (document.mozCancelFullScreen)
                    document.mozCancelFullScreen();
                else if (document.webkitExitFullscreen) 
                    document.webkitExitFullscreen();
                
                fullScreen = false;
                document.getElementById("fullScreenSlideShowWrapper").style.display = "none";
                document.getElementById("fullScreenSlideShowStarter").style.display = "block";
                fixViewportUnits(false);
            }
			
		</script>		

		<!-- Google Analytics code -->
		<script>
		  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

		  ga('create', 'UA-54231741-1', 'auto');
		  ga('send', 'pageview');

		</script>
        
        <?php			
			function getFileNamesFromFolder($folder)
			{	//echo "B ";
				$folderHandle  = opendir($folder);							
				while (false !== ($filename = readdir($folderHandle))) 
				{	//echo "C ";
					if ($filename !== "." and $filename !== "..")
					{	$files[] = $filename;
						//echo $filename;
					}
				}							
				return $files;
			}
			
			function getAllTeamImages($folder)
			{	//echo "A ";
				$teamImageFolders = getFileNamesFromFolder($folder);
				sort($teamImageFolders);
				$teamImageFolders = array_reverse($teamImageFolders);						
			
				$teamImages = array();
				foreach ($teamImageFolders as $yearFolder)
				{	
					$yearImages = getFileNamesFromFolder($folder . $yearFolder);
					sort($yearImages);
					for ($i = 0; $i < count($yearImages); $i++)
						$yearImages[$i] = $folder . $yearFolder . "/" . $yearImages[$i];
						
					if (count($teamImages) == 0)
						$teamImages = $yearImages;
					else if (count($yearImages) > 0)
						$teamImages = array_merge($teamImages, $yearImages);
				}
				return $teamImages;
			}
			
			echo "<script>";
			echo "function loadImages()";
			echo "{	";	
			
			$teamImages = getAllTeamImages("../images/team/");
			foreach ($teamImages as $image)
			{	echo "appendImage('" . $image . "');";
			}
			
			echo "}";
			echo "</script>";
		
		?>
		
	</head>
	<body data-role="none">		
		
		<div class="sidebarLeft" data-role="none">&nbsp;</div>
		
		<div class="main" data-role="none">
		
			<!-- menu content will be loaded from topbar.html -->
			<div id='topbarContent' class="topbar" data-role="none"></div>	
			
			<!------------------------------- end of topbar ------------------------------------>
			
			<div class="contentTeamPhotos" data-role="none">
				<div class="pageHeader" data-role="none"><strong>Data &amp; Multimedia</strong> | Team photos</div>	
								
				<div id="theSlideShow" class="slideshow">                    
				<!--	<img class="slideshowImage2" src="/images/team/2014/IMG_0012.jpg" data-role="none">
					<img class="slideshowImage2" src="/images/team/2014/IMG_0014.jpg" data-role="none">						-->	
				</div>
				
				<div class="slideshowControls" data-role="none">					<img src="../images/slideshow_blue_back.png" alt="" onmouseover="slideControlOver('back')" onmouseout="slideControlOut('back')" onclick="slideControlClick('back')" id="back" />
										<img src="/images/slideshow_blue_pause.png" alt="" onmouseover="slideControlOver('pause')" onmouseout="slideControlOut('pause')" onclick="slideControlClick('pause')" id="pause"  data-role="none"/>
					<img src="/images/slideshow_blue_play1.png" alt="" onmouseover="slideControlOver('play1')" onmouseout="slideControlOut('play1')" onclick="slideControlClick('play1')" id="play1"  data-role="none"/>
					<img src="/images/slideshow_orange_play2.png" alt="" onmouseover="slideControlOver('play2')" onmouseout="slideControlOut('play2')" onclick="slideControlClick('play2')" id="play2"  data-role="none"/>
					<img src="/images/slideshow_blue_play3.png" alt="" onmouseover="slideControlOver('play3')" onmouseout="slideControlOut('play3')" onclick="slideControlClick('play3')" id="play3"  data-role="none"/>
				</div>
                
              <div id="fullScreenSlideShowStarter" data-role="none"> 
                  <div id="fullScreenSlideShowStarterOverlay" data-role="none">View image gallery</div>
					<img src="/images/team/2024/AGroup2024.jpg"> 									
                </div> 
	
			</div> 	
                        
            <div id="fullScreenSlideShowWrapper" data-role="none">
                <div id="fullScreenSlideShowClose" onclick="closeFullScreen();" data-role="none">&times;</div>
                <div id="fullScreenSlideShow" data-role="none"></div>
            </div>				
			
			<!------------------------------- end of content ------------------------------------>
			
			<!-- footer content will be loaded from footer.html -->
			<div id='footerContent' class="footer" data-role="none"></div>
			
			<!------------------------------- end of footer ------------------------------------>

			<div class="credits" data-role="none">
				Website designed and coded by <a href="http://www.colinjstewart.com">Colin J Stewart</a>
			</div>			
			
		</div>
		
		<div class="sidebarRight" data-role="none">&nbsp;</div>
	</body>
</html>

