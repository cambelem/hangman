<?php

	$GLOBALS['module'] = 'hangman';
	$GLOBALS['file'] = 'hangview.tpl';

	class Hangman 
	{		
		/*
		This constructor determines the action
		and appropriately calls the functions to
		play a hangman game.
		*/
		public function __construct()
		{
			if (!isset($_GET['action']))
			{
				$_GET['action'] = 'new_game';
			}

			$action = $_GET['action'];	
			$cletters = array();

			if (isset($_SESSION['correctletters']))
			{
				$cletters = $_SESSION['correctletters'];
			}

			switch ($action)
			{				
				case 'new_game':
					$l = range('a','z');

					$tpl['correct'] = $this->ranWord();
					$tpl['pic'] = $this->basePicture();
					
					foreach ($l as $i)
					{
						$link = PHPWS_Text::moduleLink($i,'hangman', array('action'=> 'guess', 'letter'=>$i));
						$tpl['letters'][] = array('LETTER' => $link);
					}						
					break;

				case 'guess':

					$gletter = $_GET['letter'];

					$isletter = $this->findingLetters($gletter, $_SESSION['word']);	
					$list = $_SESSION['unUsedLetters'];					
					$tpl['pic'] = $this->picture($isletter);
					$tpl['correct'] = $this->displayLetters($gletter, $_SESSION['word'],$cletters);

					foreach ($list as $x)
					{
						$link2 = PHPWS_Text::moduleLink($x,'hangman', array('action'=> 'guess', 'letter'=>$x));
						$tpl['letters'][] = array('LETTER' => $link2);
					}																										
					break;

				default:
					if (!isset($_SESSION['word']))
					{
						$this->ranWord();
					}	
			}
			
			if (isset($_SESSION['correctletters']))
			{
				$cletters = $_SESSION['correctletters'];
			}

			$tpl['wlgame'] = $this->checkWinOrLoss($cletters , $_SESSION['word']);
			$winloss = $this->winLoss($cletters , $_SESSION['word']);

			if ($winloss == 0)
			{
				unset($tpl['letters']); 
			}

			$nglink = PHPWS_Text::moduleLink('New Game','hangman', array('action'=> 'new_game'));
			$tpl['ngame'][]= array('NEW_GAME' => $nglink);
	
			echo PHPWS_Template::process($tpl, $GLOBALS['module'], $GLOBALS['file']);
		}

		/*
		This function grabs a new random word and resets
		the SESSION variables back to default.
		
		return $tempArray which holds an array of '_'.			
		*/
		public function ranWord()
		{	
			$tempArray = array();
			$maxAttempts = 6;
			$_SESSION['attempts'] = ($maxAttempts);
			$_SESSION['count'] = 0;
			$_SESSION['c'] = 0;
			unset($_SESSION['correctletters']);
			unset($_SESSION['unUsedLetters']);
	
			$myfile = file_get_contents(PHPWS_SOURCE_DIR . 'mod/hangman/hangwords.txt');
			
			$words = preg_split('/[\s]+/', $myfile);
			$random = rand(0,count($words));
			$_SESSION['word'] = (strtolower($words[$random]));

			$answer = str_split($_SESSION['word']);
			for ($i = 0; $i < count($answer); $i++)
			{						
				$tempArray[$i] = array('CORRECTLETTERS' => "_ ");
			}

			return $tempArray;
		}

		/*
		This function sets the picture to 0.
		
		return $basepicture holds the first index
		position for the first picture.	
		*/
		public function basePicture()
		{
			$pic = glob('mod/hangman/img/' . "*.gif");
			$basepicture[] = array('PICTURE' => "<img src= $pic[0] />");
			return $basepicture;
		}

		/*
		This function determines which picture to
		choose from if the user guesses wrong

		return $picturearray which holds the current
		hangman picture.
		*/
		public function picture($isletter)
		{
			$pic = glob('mod/hangman/img/' . "*.gif");

			if (!$isletter)
			{
				$_SESSION['c'] = $_SESSION['c'] + 1;
				$c = $_SESSION['c'];

				if ($c < 7)
				{
					$picturearray[] = array('PICTURE' => "<img src= $pic[$c] />");
				}
				else
				{
					$picturearray[] = array('PICTURE' => "<img src= $pic[6] />");
				}					
			}
			else
			{
				$p = $_SESSION['c'];
				$picturearray[] = array('PICTURE' => "<img src= $pic[$p] />");
			}

			return $picturearray;
		}

		/*
		This function displays the letters on the screen
		for the users to choose from.

		return $correctarray that holds an array
		of letters or symbols.
		*/	
		public function displayLetters($gletter, $word, $cletters)
		{
			$answer = str_split($word);
			$correctarray = array();

			for ($i = 0; $i < count($answer); $i++)
			{
				if ($gletter == $answer[$i])
				{
					$correctarray[] = array('CORRECTLETTERS' => $gletter);
				}
				elseif (in_array($answer[$i], $cletters))
				{
					$correctarray[] = array('CORRECTLETTERS' => $answer[$i]);
				}				
				else
				{
					$correctarray[] = array('CORRECTLETTERS' => "_ ");					
				}
			}
			return $correctarray;
		}

		/*
		This function grabs the guess and the word, 
		splits the word and then compares the guess
		to each character.

		return $inWord that holds a boolean value.
		*/
		public function findingLetters($guess, $word)
		{
			$letters = range('a','z');
			$inWord = false;
			$usedLetters = array();
			$answer = str_split($word);

			for ($i = 0; $i < count($answer); $i++)
			{
				if ($guess == $answer[$i])
				{
					$inWord = true;
					$_SESSION['correctletters'][] = $guess;
					$usedLetters[$_SESSION['count']] = $guess;
				}

			}

			$_SESSION['count'] = $_SESSION['count'] + 1;
			
			if ($inWord == false)
			{
				$_SESSION['attempts'] = $_SESSION['attempts'] - 1;
			}
			

			if (!isset($_SESSION['unUsedLetters']))
			{
				$unUsedLetters = $letters;
			}
			else
			{
				$unUsedLetters = $_SESSION['unUsedLetters'];
			}

			foreach ($unUsedLetters as &$letter)
			{
				if ($letter == $guess)
				{
					$letter = 0;			
				}
			}
			$_SESSION['unUsedLetters'] = $unUsedLetters;
		
			return $inWord;
		}

		/*
		This function compares the guess
		and the word to determine if the player wins 
		or lose and displays the correct response.

		return $winloserarray that holds a string
		determining if the player won or lost
		*/
		public function checkWinOrLoss($correctarray,$word)
		{
			$phrase = '<font color="red">';
			if ($_SESSION['attempts'] == 0)
			{				 
				$winlosearray[]= array('WIN_LOSS' => "You $phrase lost</font>! <br> The word was $word.");	
				return $winlosearray;
			}
			
			if (count($correctarray) == count(str_split($word)))
			{				 
				$winlosearray[]= array('WIN_LOSS' => "You win!");
				return $winlosearray;						
			}		
		}
		
		/*
		This function compares the guess
		and the word to determine if the player wins 
		or lose and then returns a 0 or 1 to unset
		the linked letters.

		return int returns a 0 or 1;
		*/
		public function winLoss($correctarray,$word)
		{
			if ($_SESSION['attempts'] == 0)
			{
				return 0;
			}
			
			if (count($correctarray) == count(str_split($word)))
			{
				return 0;
			}

			return 1;
		}
	}

?>
