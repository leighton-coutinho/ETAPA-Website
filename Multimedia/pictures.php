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
		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>		
		
		<script>
			// loads the menu and footer from topbar.html and footer.html
			
			//$(document).ready(function(e) {$('#topbarContent').load('/topbar.html');});
			//$(document).ready(function(e) {$('#footerContent').load('/footer.html');});-->
		</script>
		
		<script>
			var globalSlideShowState = "play2";			
			var globalTimer = -1;			
			
			$( document ).ready(function() {
				// loads the menu and footer from topbar.html and footer.html
				$('#topbarContent').load('/topbar.html');
				$('#footerContent').load('/footer.html');
				
				startSlideShow(3000);				
				loadImages();
			});
			
			function startSlideShow(speed)
			{						
				$(function()
				{	$('.slideshow > :gt(0)').hide();
					globalTimer = setInterval(function()					
					{
						$('.slideshow > :first-child').fadeOut().next().fadeIn().end().appendTo('.slideshow'); 
					}, speed);												
				});					
			}	

			function slideControlOver(control)
			{	document.getElementById(control).src = "../images/slideshow_black_" + control + ".png";
			}
			
			function slideControlOut(control)
			{	var newColour = "";
				if (globalSlideShowState == control)
					newColour = "orange";
				else
					newColour = "blue";				
				
				document.getElementById(control).src = "../images/slideshow_" + newColour + "_" + control + ".png";
			}
			
			function slideControlClick(control)
			{	clearInterval(globalTimer);	
			
				if (control == "back")
				{	$('#theSlideShow > :first-child').fadeOut();
					$('#theSlideShow > :last-child').prependTo('#theSlideShow');
					$('#theSlideShow > :first-child').fadeIn();
				}
				else if (control == "play1")
					startSlideShow(5000)
				else if (control == "play2")
					startSlideShow(3000)
				else if (control == "play3")
					startSlideShow(1500)
				
				// update buttons
				document.getElementById(globalSlideShowState).src = "../images/slideshow_blue_" + globalSlideShowState + ".png";
				globalSlideShowState = control;
				document.getElementById(globalSlideShowState).src = "../images/slideshow_orange_" + globalSlideShowState + ".png";
			}
			
			function appendImage(imageName)
			{	var node = document.createElement('img');
				node.setAttribute('class', 'slideshowImage2');
				node.setAttribute('src', imageName);				
				document.getElementById('theSlideShow').appendChild(node);		
			}
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
	

		<!-- Google Analytics code -->
		<script>
		  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

		  ga('create', 'UA-54231741-1', 'auto');
		  ga('send', 'pageview');

		</script>
		
	</head>
	<body>		
		
		<div class="sidebarLeft">&nbsp;</div>
		
		<div class="main">
		
			<!-- menu content will be loaded from topbar.html -->
			<div id='topbarContent' class="topbar"></div>	
			
			<!------------------------------- end of topbar ------------------------------------>
			
			<div class="content">
				<div class="pageHeader"><strong>Data &amp; Multimedia</strong> | Team photos</div>	
			</div>			
			<div class="contentTeamPhotos">						
				
				<div id="theSlideShow" class="slideshow">
				</div> 				
				
			<div class="slideshowControls">
					<img src="../images/slideshow_blue_back.png" alt="" onmouseover="slideControlOver('back')" onmouseout="slideControlOut('back')" onclick="slideControlClick('back')" id="back" />
					<img src="../images/slideshow_blue_pause.png" alt="" onmouseover="slideControlOver('pause')" onmouseout="slideControlOut('pause')" onclick="slideControlClick('pause')" id="pause" />
					<img src="../images/slideshow_blue_play1.png" alt="" onmouseover="slideControlOver('play1')" onmouseout="slideControlOut('play1')" onclick="slideControlClick('play1')" id="play1" />
					<img src="../images/slideshow_orange_play2.png" alt="" onmouseover="slideControlOver('play2')" onmouseout="slideControlOut('play2')" onclick="slideControlClick('play2')" id="play2" />
					<img src="../images/slideshow_blue_play3.png" alt="" onmouseover="slideControlOver('play3')" onmouseout="slideControlOut('play3')" onclick="slideControlClick('play3')" id="play3" />
				</div> 
			
			</div> 
						
			
			<!------------------------------- end of content ------------------------------------>
			
			<!-- footer content will be loaded from footer.html -->
			<div id='footerContent' class="footer"></div>
			
			<!------------------------------- end of footer ------------------------------------>

			<div class="credits">
				Website designed and coded by <a href="http://www.colinjstewart.com">Colin J Stewart</a>
			</div>			
			
		</div>
		
		<div class="sidebarRight">&nbsp;</div>
	</body>
</html>

