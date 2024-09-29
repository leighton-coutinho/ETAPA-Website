<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>	
		<title>TRAM: Transportation Research at McGill</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">		

		<link rel="stylesheet" href="reset.css" media="screen,projection" type="text/css" />
		<link rel="stylesheet" type="text/css" href="../style.css">
		<link rel="shortcut icon" href="/images/tramIcon.ico">
		
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>								
		<script>
			// loads the menu and footer from topbar.html and footer.html
			$(document).ready(function(e) {$('#topbarContent').load('/topbar.html');});
			$(document).ready(function(e) {$('#footerContent').load('/footer.html');});
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
		
	</head>
	<body>

	<div class="sidebarLeft">&nbsp;</div>
		
		<div class="main">
		
			<!-- menu content will be loaded from topbar.html -->
			<div id='topbarContent' class="topbar"></div>	
			
			<!------------------------------- end of topbar ------------------------------------>
			
			<div class="content">
			
				<div class="pageHeader"><strong>Search</strong><a name="top"></a></div>				

				<?php 						
					include('class.pdf2text.php');
					
					class SearchResult
					{	var $filename = "";
						var $title = "";
						var $snippet = "";
						var $score = 0;						
					}
					
					/**********************************************************************************************/
					
					function getFileNamesFromFolder($folder)
					{	$folderHandle  = opendir($folder);							
						while (false !== ($filename = readdir($folderHandle))) 
						{	if ($filename !== "." and $filename !== "..")
								$files[] = $filename;
						}							
						return $files;
					}
					
					/**********************************************************************************************/
					
					function getAllOtherFiles($startFolder)
					{	$files = getFileNamesFromFolder($startFolder);
						foreach ($files as $filename)
						{	$search = strpos($filename, ".");
							if ($search == false)  // folder name; recurse
							{	if ($filename !== "images" and $filename !== "Publications" and $filename !== "Teaching")
								{	echo "<div><em>" . $filename . "</em></p>";
									getAllOtherFiles($startFolder . $filename . "/");
								}
							}
							else
							{	$ext = pathinfo($filename, PATHINFO_EXTENSION);
								if ($ext == "html" or $ext == "pdf")
									echo $filename . "<br/>";
							}
						}
					}
					
					/**********************************************************************************************/
					
					function removeExtension($filename)
					{	$dot = strpos($filename, ".");
						return substr($filename, 0, $dot);
					}
					
					/**********************************************************************************************/
					
					function convertPDFtoTxt($pdfFile, $folder)
					{	$pdfText = getPDFText($folder . $pdfFile);
						file_put_contents($folder . "txt/" . $pdfFile . ".txt", $pdfText);
					}
					
					/**********************************************************************************************/
					
					function getPDFText($pdfFile)
					{	$pdf2Txt = new PDF2Text();
						$pdf2Txt->setFilename($pdfFile);
						$pdf2Txt->decodePDF();						
						return stripChinese(stripNonAscii($pdf2Txt->output()));						
					}
					
					function stripNonAscii($text)
					{	
						$newText = "";
						for ($i = 0; $i < strlen($text); $i++)
						{	$ascii = ord($text[$i]);
							if ($ascii >= 32 and $ascii <= 126)
								$newText .= $text[$i];
						}
						return $newText;
					}
					
					function stripChinese($text)
					{	
						$newText = "";
						$i = 0; 						
						while ($i < strlen($text))
						{	$index = strpos($text, "&#x", $i);
							if ($index !== false)
							{	
								$newText .= slice($text, $i, $index-1);
								$i = $index + 8;
							}
							else
							{	$newText .= slice($text, $i, -1);
								break;
							}
						}
						return $newText;
					}
					
					/**********************************************************************************************/
					
					function convertPDFsToTxt($pdfFiles, $folder)
					{	$txtFolder = $folder . "txt/";
						if (file_exists($txtFolder) !== false)
						{							
							foreach ($pdfFiles as $pdfFile)
							{	// corresponding txt file doesn't exist; create it now
								if (file_exists($txtFolder . $pdfFile . ".txt") == false)
								{	convertPDFtoTxt($pdfFile, $folder);
								}								
							}
						}
						else  // no txt files exist; make them all now
						{	
							foreach ($pdfFiles as $pdfFile)
							{	convertPDFtoTxt($pdfFile, $folder);
							}
						}
					}
					
					/**********************************************************************************************/
					
					function getFileTypes($files, $type)
					{	//$results[];
						foreach($files as $file)
						{	if (pathinfo($file, PATHINFO_EXTENSION) == $type)
								$correctFiles[] = $file;
						}
						return $correctFiles;
					}
					
					/**********************************************************************************************/
					
					function slice($string, $from, $to)
					{	$length = strlen($string);
						
						if ($from < 0)
							$from = $length + $from;
						if ($to < 0)
							$to = $length + $to;
							
						$substrLength = $to - $from + 1;
						return substr($string, $from, $substrLength);
					}
					
					/**********************************************************************************************/
					
					function getTitlesFromHTML($htmlFile, $titleMap)
					{	$htmlText = file_get_contents($htmlFile);
						
						$index = 0;
						while ($index < strlen($htmlText))
						{	$divIndex = strpos($htmlText, '<div class="publication">', $index+1);
							
							if ($divIndex == false)
								$divIndex = strpos($htmlText, '<div class="presentation">', $index+1);
							
							if ($divIndex == false)
								break;
						
							$quoteIndex1 = strpos($htmlText, '"', $divIndex+26);
							$quoteIndex2 = strpos($htmlText, '"', $quoteIndex1+1);
							$pdfFile = substr($htmlText, $quoteIndex1+1, $quoteIndex2 - $quoteIndex1 - 1);
							
							$slashIndex = strpos($pdfFile, "/");  // strip any folder names in the URL
							/*echo "AA " . $slashIndex . "BB<br/>";
							echo $pdfFile . "<br/>";*/
							if ($slashIndex !== false)
								$pdfFile = slice($pdfFile, $slashIndex+1, -1);
							/*echo $pdfFile . "<br/>";
							echo $htmlFile . "<br/><br/>";*/
							
							$bracketIndex1 = strpos($htmlText, '>', $quoteIndex2+1);
							$bracketIndex2 = strpos($htmlText, '<', $bracketIndex1+1);
							$pdfTitle = substr($htmlText, $bracketIndex1+1, $bracketIndex2 - $bracketIndex1 - 1);								
							
							$titleMap[$pdfFile] = $pdfTitle;
							$index = $bracketIndex2 + 1;								
						}
						return $titleMap;
					}
					
					/**********************************************************************************************/
					
					function removeNonTitledPDFs($pdfFiles, $titleMap)
					{	
						foreach ($pdfFiles as $pdfFile)
						{	$title = $titleMap[$pdfFile];
							if ($title == NULL)
							{	
							}
							else
								$pdfFiles2[] = $pdfFile;
						}							
						return $pdfFiles2;
					}
					
					/**********************************************************************************************/
					
					function endsWith($str1, $str2)
					{	$ending = slice($str1, strlen($str1)-strlen($str2), -1);						
						if ($ending == $str2)
							return true;
						else
							return false;
					}
					
					/**********************************************************************************************/
					
					function getNextWord($text, $words, $index)
					{	$minIndex = strlen($text);
						$nextWord = "";
						foreach ($words as $word)
						{	//$wordIndex = strpos($text, $word, $index);
							$wordIndex = wholeWordSearch($text, $word, $index);
							if ($wordIndex == false)
							{	$variant = getVariant($word);
								if ($variant !== "")
								{	//$wordIndex = strpos($text, $variant, $index);
									$wordIndex = wholeWordSearch($text, $variant, $index);
									if ($wordIndex !== false and $wordIndex < $minIndex)
									{	$minIndex = $wordIndex;
										$nextWord = $variant;
									}
								}
							}
							else if ($wordIndex !== false and $wordIndex < $minIndex)
							{	$minIndex = $wordIndex;
								$nextWord = $word;
							}
						}
						return $nextWord;
					}
					
					/**********************************************************************************************/					
					
					function getSnippet($text, $searchTerm, $index)
					{	
						if ($searchTerm[0] == '"' and $searchTerm[strlen($searchTerm)-1] == '"')  // exact search
							$searchTerm = slice($searchTerm, 1, -2);  // remove quotes
							
						$searchTermLength = strlen($searchTerm);
					
						$snipStart = $index - 50;
						if ($snipStart < 0)
							$snipStart = 0;						
						
						$snipEnd = $index + $searchTermLength + 50;
						if ($snipEnd > strlen($text))
							$snipEnd = strlen($text) - 1;						
						
						$snippet = slice($text, $snipStart, $snipEnd);
						return "..." . highlightWords($snippet, $searchTerm) . "...";
					}
					
					/**********************************************************************************************/
					
					function highlightWords($text, $words)
					{	
						if ($words[0] == '"' and $words[strlen($words)-1] == '"')  // exact search
						{	$words = slice($words, 1, -2);  // remove quotes
							//echo "AA" . $words . "BB<br/>";
						}
						
						$theWords = explode(" ", $words);						
						$textLower = strtolower($text);
						$newText = "";
						
						$i = 0;
						while ($i < strlen($text))
						{
							$nextWord = getNextWord($textLower, $theWords, $i);								
							if ($nextWord == "")
							{	$newText .= slice($text, $i, -1);
								break;
							}
							
							//$index = strpos($textLower, $nextWord, $i+1);							
							$index = wholeWordSearch($textLower, $nextWord, $i+1);							
							
							$newText .= slice($text, $i, $index-1) . "<em>" . slice($text, $index, $index+strlen($nextWord)-1) . "</em>";
							$i = $index+strlen($nextWord);
						}							
						return $newText;
					}
					
					/**********************************************************************************************/
					
					function getVariant($word)
					{
						$variant = "";
						if (endsWith($word, "s"))
							$variant = slice($word, 0, -2);
						else if (endsWith($word, "ed"))
							$variant = slice($word, 0, -3);
						else if (endsWith($word, "ing"))
							$variant = slice($word, 0, -4);
						return $variant;
					}
					
					/**********************************************************************************************/
					
					function searchText($text, $searchTerm, $titleSearch = false)
					{							
						$result = new SearchResult();	
						$result->score = 0;	
						$textLower = strtolower($text);
								
						$wholeTermSearch = false;
						$oneWordSearch = false;
						if ($searchTerm[0] == '"' and $searchTerm[strlen($searchTerm)-1] == '"')  // exact search
						{	$wholeTermSearch = true;
							$searchTerm = slice($searchTerm, 1, -2);  // remove quotes							
						}
						else
						{	$spaceIndex = strpos($searchTerm, " ");
							if ($spaceIndex == false)  // there's no space; therefore just one word
							{	$wholeTermSearch = true;
								$oneWordSearch = true;
							}
						}
						
						// do whole term search in all situations
						//$index = strpos($textLower, $searchTerm);						
						$index = wholeWordSearch($textLower, $searchTerm);						
						if ($index !== false)
						{	
							$result->snippet = getSnippet($text, $searchTerm, $index); 
							$result->score = 10;							
						}
						else if ($oneWordSearch == true) // check word variations
						{	
							$variant = getVariant($searchTerm);							
							if ($variant !== "")
							{	
								//$index = strpos($textLower, $variant);
								$index = wholeWordSearch($textLower, $variant);
								if ($index !== false)
								{	
									$result->snippet = getSnippet($text, $variant, $index); 
									$result->score = 10;							
								}
							}
						}
						
						
						// multi word search only if there are multiple words without quotes
						// if exact search was found, ignore
						if ($wholeTermSearch == false and $result->score == 0)  
						{								
							$searchWords = explode(" ", $searchTerm);
							
							foreach ($searchWords as $searchWord)
							{	
								$variant = getVariant($searchWord);
								
								// first check existing snippet
								
								//$index = strpos(strtolower($result->snippet), $searchWord);
								$index = wholeWordSearch(strtolower($result->snippet), $searchWord);
								
								if ($index !== false)
								{	
									$result->snippet = highlightWords($result->snippet, $searchWord); 
									$result->score += 1;
								}
								//else if (strpos(strtolower($result->snippet), $variant) !== false) // check variant
								else if (wholeWordSearch(strtolower($result->snippet), $variant) !== false) // check variant
								{	
									$result->snippet = highlightWords($result->snippet, $variant); 
									$result->score += 1;
								}
								else
								{	// search the actual text
									
									//$index = strpos($textLower, $searchWord);
									$index = wholeWordSearch($textLower, $searchWord);
									if ($index !== false)
									{	$result->snippet .= getSnippet($text, $searchWord, $index); 
										$result->score += 1;										
									}
									else 
									{	
										//$index = strpos(strtolower($textLower), $variant);
										$index = wholeWordSearch(strtolower($textLower), $variant);
										if ($index !== false) // check variant
										{
											$result->snippet .= getSnippet($text, $variant, $index); 
											$result->score += 1;										
										}
									}
								}
							}
							
							if ($titleSearch == false and $result->score < count($searchWords))  // cull the weakest matches
							{	$result->score = 0;
							}
						
						}
						else if ($wholeTermSearch == false and $result->score == 10)  // highlight the individual words in the snippet
						{	
							$searchWords = explode(" ", $searchTerm);
							foreach ($searchWords as $searchWord)
								$result->snippet = highlightWords($result->snippet, $searchWord);
							
						}
						return $result;
					}
					
					/**********************************************************************************************/
					
					function sortResults($searchResults)
					{							
						for ($i = 1; $i < count($searchResults); $i++)
						{	$j = $i;
							while ($j > 0 and $searchResults[$j]->score > $searchResults[$j-1]->score)
							{	$temp = $searchResults[$j-1];
								$searchResults[$j-1] = $searchResults[$j];
								$searchResults[$j] = $temp;
								$j--;									
							}
						}
						return $searchResults;
					}
					
					/**********************************************************************************************/
					
					function pdfSearch($searchTerm, $folder, $titleMap)
					{							
						// make sure all PDFs have a TXT equivalent
						$files = getFileNamesFromFolder($folder);
						$pdfFiles = getFileTypes($files, "pdf");													
						convertPDFsToTxt($pdfFiles, $folder);
						
						// search TXT files							
						foreach ($pdfFiles as $pdfFile)
						{	//$text = stripChinese(stripNonAscii(file_get_contents($folder . "txt/" . $pdfFile . ".txt")));
							$text = file_get_contents($folder . "txt/" . $pdfFile . ".txt");
							$result = searchText($text, $searchTerm); 
							
							if ($result->score > 0 or strlen($text) == 0)
							{
								$title = $titleMap[$pdfFile];
								if ($title == null)
									$result->title = $pdfFile;
								else
									$result->title = $title;							
								
								$titleResults = searchText($result->title, $searchTerm, true);								
								$result->score += ($titleResults->score * 3);																
									
								if ($result->score > 0)
								{	$result->filename = slice($folder, 1, -1) . $pdfFile;
									$searchResults[] = $result;
								}
								$result->title = highlightWords($result->title, $searchTerm);
							}
						}
						return $searchResults;	
					}	

					/**********************************************************************************************/					
					
					function searchPublications($searchTerm)
					{
						$publicationFolder = "./Research/Publications/";
						
						// get PDF titles from HTML files
						$titleMap[""] = "";
						
						$titleMap = getTitlesFromHTML($publicationFolder . "publicTransit.html", $titleMap);
						$titleMap = getTitlesFromHTML($publicationFolder . "performanceMeasuresAccessibility.html", $titleMap);
						$titleMap = getTitlesFromHTML($publicationFolder . "cyclingWalking.html", $titleMap);
						$titleMap = getTitlesFromHTML($publicationFolder . "travelBehaviour.html", $titleMap);
						$titleMap = getTitlesFromHTML($publicationFolder . "other.html", $titleMap);					
						
						return pdfSearch($searchTerm, $publicationFolder, $titleMap);						
					}
					
					/**********************************************************************************************/
					
					function searchStudentPapers($searchTerm)
					{
						$studentPaperFolder1 = "./Teaching/srp/documents/";														
						$titleMap1[""] = "";
						$titleMap1 = getTitlesFromHTML("./Teaching/srp/srp.html", $titleMap1);											
						
						$studentPaperFolder2 = "./Teaching/PhD/dissertations/";							
						$titleMap2[""] = "";
						$titleMap2 = getTitlesFromHTML("./Teaching/PhD/PhD.html", $titleMap2);
						
						return array_merge(pdfSearch($searchTerm, $studentPaperFolder1, $titleMap1), 
										   pdfSearch($searchTerm, $studentPaperFolder2, $titleMap2));
					}
					
					/**********************************************************************************************/
					
					function searchSeminars($searchTerm)
					{
						$seminarFolder = "./Teaching/seminar/presentations/";														
						$titleMap1[""] = "";
						$titleMap2[""] = "";
						$titleMap1 = getTitlesFromHTML("./Teaching/seminar/current.html", $titleMap1);					
						$titleMap2 = getTitlesFromHTML("./Teaching/seminar/archived.html", $titleMap2);											
						$titleMap = array_merge($titleMap1, $titleMap2);
																								
						
						return pdfSearch($searchTerm, $seminarFolder, $titleMap);
					}
					
					/**********************************************************************************************/
					
					function getHTMLPageTitle($htmlText, $fullTitle = true)
					{	$titleMarker = '<div class="pageHeader"><strong>';
						if ($fullTitle == false)
							$titleMarker = '<div class="pageHeader"><strong>Teaching</strong> | ';					
							
						$divIndex = strpos($htmlText, $titleMarker);
						$divIndex2 = strpos($htmlText, "</div>", $divIndex+1);
						$title = slice($htmlText, $divIndex+strlen($titleMarker), $divIndex2-1);						
						
						return $title;						
					}
					
					/**********************************************************************************************/
					
					function cleanHTMLText($htmlText)
					{	$bodyIndex = strpos($htmlText, "<body>");
						$htmlText = slice($htmlText, $bodyIndex);
						
						// strip all tags
						$tagFreeText = "";
						
						$i = 0;
						while ($i < strlen($htmlText))
						{	$bracketIndex1 = strpos($htmlText, ">", $i+1);
							if ($bracketIndex1 === false)
								break;
							
							$bracketIndex2 = strpos($htmlText, "<", $bracketIndex1);
							if ($bracketIndex2 === false)
								break;
								
							$tagFreeText .= slice($htmlText, $bracketIndex1+1, $bracketIndex2-1) . " ";
							$i = $bracketIndex2 + 1;								
						}						
						return $tagFreeText;
					}
					
					/**********************************************************************************************/
					
					function searchAllHTMLandPDF($parentFolder, $subFolder, $searchTerm, $fullTitle = true)
					{	
						$folder = $parentFolder . $subFolder;
						$filenames = getFileNamesFromFolder($folder);						
						//print_r($filenames);
							
						foreach ($filenames as $filename)
						{	$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
							//$fullPath = $coursesFolder . $folderName . "/" . $filename;
							$fullPath = $folder . "/" . $filename;
							if ($ext == "html")
							{	$htmlText = file_get_contents($fullPath);										
								$cleanHTMLText = cleanHTMLText($htmlText);
								//echo "A ";
								
								$result = searchText($cleanHTMLText, $searchTerm);								
								if ($result->score > 0)  // success!
								{	
									//echo $result->score . " -- " . $filename . " A<br/>";
									$result->title = getHTMLPageTitle($htmlText, $fullTitle);
									$titleResults = searchText($result->title, $searchTerm, true);
									$result->title = highlightWords($result->title, $searchTerm);
									$result->score += ($titleResults->score * 3);					
																	
									$result->filename = slice($fullPath, 1, -1);
									$searchResults[] = $result;											
								}
							}
							else if ($ext == "pdf")
							{	$pdfText = getPDFText($fullPath);
								$result = searchText($pdfText, $searchTerm);
								
								if ($result->score > 0)  // success!
								{	//echo $result->score . " B<br/>";
									$result->filename = slice($fullPath, 1, -1);
									
									$result->title = $subFolder . "/" . $filename;
									//$result->title = $folder . "/" . $filename;
									$titleResults = searchText($result->title, $searchTerm, true);
									$result->title = highlightWords($result->title, $searchTerm);
									$result->score += ($titleResults->score * 3);
									
									$searchResults[] = $result;
								}
							}
						}
						return $searchResults;
					
					}
					
					/**********************************************************************************************/
					
					function searchCourses($searchTerm)
					{
						$coursesFolder = "./Teaching/";														
						$titleMap[""] = "";
						$folderNames = getFileNamesFromFolder($coursesFolder);
						$searchResults = array();
						
						foreach ($folderNames as $folderName)
						{	$prefix = slice($folderName, 0, 3);
							//echo $folderName . ": " . $prefix . "<br/>";
							if ($prefix == "URBP")  // course folder! explore!
							{	//$filenames = getFileNamesFromFolder($coursesFolder . $folderName);
								$results = searchAllHTMLandPDF($coursesFolder, $folderName, $searchTerm, false);
								if (count($results) > 0)
								{	$searchResults = array_merge($searchResults, $results);									
								}
							}							
						}						
						return $searchResults;
					}
					
					function getImmediateFolder($fullPath)
					{	$i = strlen($fullPath) - 2;						
						while ($i >= 0)
						{	if ($fullPath[$i] == "/")
							{	$folder = slice($fullPath, $i, -1);								
								return $folder;
							}
							$i--;
						}						
						return $fullPath;
					}
					
					function searchAllOtherFiles($searchTerm, $folder)
					{	
						$files = getFileNamesFromFolder($folder);
						
						// search this folder ----------------------------------------------------------
						$subFolder = "";
						$parentFolder = $folder;								
						if ($folder !== "./")						
						{	$subFolder = getImmediateFolder($folder);
							$parentFolder = slice($folder, 0, strpos($folder, $subFolder)-1);
						}			
						//echo $folder . " -- " . $parentFolder . " -- " . $subFolder . "<br/>";
						$allResults = searchAllHTMLandPDF($parentFolder, $subFolder, $searchTerm, true);						
						
						// recurse into subfolders ----------------------------------------------------------
						foreach ($files as $filename)
						{	
							$index = strpos($filename, ".");								
							if ($index == false)  // folder name; recurse
							{	if ($filename !== "images" and $filename !== "Publications" and $filename !== "Teaching" and $filename !== "posters")
								{	
									$results = searchAllOtherFiles($searchTerm, $folder . $filename);
																		
									if (count($results) > 0 and count($allResults) > 0)
										$allResults = array_merge($allResults, $results);
									else if (count($results) > 0)
										$allResults = $results;									
								}
							}
						}						
						return $allResults;						
					}
					
					function wholeWordSearch($text, $word, $start = 0)
					{							
						$index = strpos($text, $word, $start);						
						if ($index !== false)
						{	
							if ($index != 0)  // check for character before
							{	$charBefore = $text[$index-1];								
								if ($charBefore !== " " and $charBefore !== "." and $charBefore !== "!" and $charBefore !== "?"  and $charBefore !== ","  and $charBefore !== ";"  and $charBefore !== ":"  and $charBefore !== '"'   and $charBefore !== '-')
									//return false;
									return wholeWordSearch($text, $word, $index + 1);
							}
							
							if (($index + strlen($word)) != (strlen($text)))  // check for character after
							{	$charAfter = $text[$index+strlen($word)];								
								if ($charAfter !== " " and $charAfter !== "." and $charAfter !== "!" and $charAfter !== "?"  and $charAfter !== ","  and $charAfter !== ";"  and $charAfter !== ":"  and $charAfter !== '"'  and $charAfter !== '-')
									//return false;								
									return wholeWordSearch($text, $word, $index + 1);
							}							
							return $index;
						}
						else
						{	return false;
						}
					}

					
					/**************************************************************************************************************/
					/**************************************************************************************************************/
					/**************************************************************************************************************/
					/**************************************************************************************************************/
									
					
					/////////////// PROCESS THE SEARCH TERM ////////////////////////////////////////////					
					
					$searchTerm = trim($_GET["search"]);					
					$searchTermLower = strtolower($searchTerm);
					
					echo "<br/><br/><br/><br/><br/>";
					echo "**" . $searchTermLower . "**<br/>";
					
					// remove silly words
					if ($searchTermLower[0] !== '"' or $searchTermLower[strlen($searchTermLower)-1] !== '"')  // not an exact match
					{	
						$exclusions = array("a", "an", "as", "at", "but", "by", "for", "from", "in", "into", "of", "on", "onto", "than", "the", "to", "with");
						foreach ($exclusions as $excl)
						{	$index = strpos($searchTermLower, $excl);
							if ($index !== false)
							{	
								$ignore = false;
								$prefix = "";
								if ($index != 0)  // check for space at front
								{	if ($searchTermLower[($index-1)] == " ")
										//$excl = " " . $excl;
										$prefix = " ";
									else
										$ignore = true;
								}
								$suffix = "";
								if (($index + strlen($excl)) != (strlen($searchTermLower)))  // check for space at end
								{	if ($searchTermLower[($index+strlen($excl))] == " ")
										//$excl = $excl . " ";
										$suffix = " ";
									else
										$ignore = true;
								}
								
								if ($ignore == false)
								{	$searchTermLower = str_replace(($prefix . $excl . $suffix), " ", $searchTermLower);
								}	
							}
						}
					}
					echo "@@" . $searchTermLower . "@@<br/>";					
					
					$totalResults = 0;
					if ($searchTermLower !== "")  // ignore empty searches
					{
						/////////////// DO THE SEARCH! ////////////////////////////////////////////
						
						$publicationResults = searchPublications($searchTermLower);
						$publicationResults = sortResults($publicationResults);						
						$numPublicationsResults = count($publicationResults);
						
						$coursesResults = searchCourses($searchTermLower);
						$coursesResults = sortResults($coursesResults);						
						$numCoursesResults = count($coursesResults);
						
						$studentPapersResults = searchStudentPapers($searchTermLower);
						$studentPapersResults = sortResults($studentPapersResults);						
						$numStudentPapersResults = count($studentPapersResults);
						
						$seminarsResults = searchSeminars($searchTermLower);
						$seminarsResults = sortResults($seminarsResults);						
						$numSeminarsResults = count($seminarsResults);
						
						$otherResults = searchAllOtherFiles($searchTermLower, "./");
						$otherResults = sortResults($otherResults);						
						$numOtherResults = count($otherResults);
						
						
						$totalResults = $numPublicationsResults + $numStudentPapersResults + $numSeminarsResults + $numCoursesResults + $numOtherResults;
					}
					
					echo '<div class="section">';
					if ($totalResults == 0)
					{	echo '<div class="paragraph">No results for <em>' . $searchTerm . '</em></div>';
						echo '</div>';
					}
					else
					{	echo '<div class="paragraph"><em>' . $totalResults . '</em> results found for <em>' . $searchTerm . '</em> in the following categories:</div>';
						echo '<ul class="blackSearch">';
						
						if ($numPublicationsResults > 0)
							echo '<li><a href="#publications">Publications</a> (' . $numPublicationsResults . ' results)</li>';						
						if ($numCoursesResults > 0)						
							echo '<li><a href="#courses">Courses</a> (' . $numCoursesResults . ' results)</li>';	
						if ($numStudentPapersResults > 0)						
							echo '<li><a href="#papers">Supervised Research Projects &amp; Dissertations</a> (' . $numStudentPapersResults . ' results)</li>';						
						if ($numSeminarsResults > 0)						
							echo '<li><a href="#seminars">Seminars</a> (' . $numSeminarsResults . ' results)</li>';							
						if ($numOtherResults > 0)						
							echo '<li><a href="#other">All other pages</a> (' . $numOtherResults . ' results)</li>';	
						echo '</ul></div>';  // end of section
						
						/************************************************************************************************************/
						/************************************************************************************************************/
												
						if ($numPublicationsResults > 0)
						{
							echo '<div class="section">';
							echo '<div class="pageSubheader"><strong>Publications</strong><a name="publications"></a></div>';
							foreach ($publicationResults as $result)
							{	echo '<div class="searchResult">';
								echo '<a href="' . $result->filename . '">' . $result->title . '</a><br/>';
								echo $result->snippet; // . "<br/>";
								//echo "Score: " . $result->score;
								echo '</div>';
							}
							echo '</div>';
						}
						
						if ($numCoursesResults > 0)
						{
							echo '<div class="section">';
							echo '<div class="pageSubheader"><strong>Courses</strong><a name="courses"></a></div>';
							foreach ($coursesResults as $result)
							{	echo '<div class="searchResult">';
								echo '<a href="' . $result->filename . '">' . $result->title . '</a><br/>';
								echo $result->snippet; // . "<br/>";
								//echo "Score: " . $result->score;
								echo '</div>';
							}
							echo '</div>';
						}
						
						if ($numStudentPapersResults > 0)
						{
							echo '<div class="section">';
							echo '<div class="pageSubheader"><strong>Supervised Research Projects &amp; Dissertations</strong><a name="papers"></a></div>';
							foreach ($studentPapersResults as $result)
							{	echo '<div class="searchResult">';
								echo '<a href="' . $result->filename . '">' . $result->title . '</a><br/>';
								echo $result->snippet; // . "<br/>";
								//echo "Score: " . $result->score;
								echo '</div>';
							}
							echo '</div>';
						}
						
						if ($numSeminarsResults > 0)
						{
							echo '<div class="section">';
							echo '<div class="pageSubheader"><strong>Seminars</strong><a name="seminars"></a></div>';
							foreach ($seminarsResults as $result)
							{	echo '<div class="searchResult">';
								echo '<a href="' . $result->filename . '">' . $result->title . '</a><br/>';
								echo $result->snippet; // . "<br/>";
								//echo "Score: " . $result->score;
								echo '</div>';
							}
							echo '</div>';
						}
						
						if ($numOtherResults > 0)
						{
							echo '<div class="section">';
							echo '<div class="pageSubheader"><strong>All other pages</strong><a name="other"></a></div>';
							foreach ($otherResults as $result)
							{	echo '<div class="searchResult">';
								echo '<a href="' . $result->filename . '">' . $result->title . '</a><br/>';
								echo $result->snippet; // . "<br/>";
								//echo "Score: " . $result->score;
								echo '</div>';
							}
							echo '</div>';
						}
					}
						
					
				?>
									
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
