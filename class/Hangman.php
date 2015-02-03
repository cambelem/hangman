<?php


	class Hangman 
	{		

		private $correct;
		private $pic;
		private $list;
		private $wlgame; 
		private $hidew; 
		private $action;

		/*
		This constructor determines the action
		and appropriately calls the functions to
		play a hangman game.
		*/
		public function __construct()
		{
			$list = array();
			$this->hidew = false;

			if (!isset($_GET['action']))
			{
				$_GET['action'] = 'new_game';
			}

			$this->action = $_GET['action'];	
			$cletters = array();

			if (isset($_SESSION['correctletters']))
			{
				$cletters = $_SESSION['correctletters'];
			}

			switch ($this->getAction())
			{				
				case 'new_game':
					
					$this->correct = $this->ranWord();
					$this->pic = $this->basePicture();
							
					break;

				case 'guess':

					$gletter = $_GET['letter'];
					$isletter = $this->findingLetters($gletter, $_SESSION['word']);	
					$this->list = $_SESSION['unUsedLetters'];					
					$this->pic = $this->picture($isletter);
					$this->correct = $this->displayLetters($gletter, $_SESSION['word'],$cletters);
																								
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

			$this->wlgame = $this->checkWinOrLoss($cletters , $_SESSION['word']);
			$this->hidew = $this->hideWords($cletters , $_SESSION['word']);

		}


		/*
			Accessors to grab the variables needed to 
			run the Hangman game.

			return all field variables.
		*/
		public function getCorrect()
		{
			return $this->correct;
		}

		public function getPic()
		{
			return $this->pic;
		}

		public function getList()
		{
			return $this->list;
		}

		public function getWLGame()
		{
			return $this->wlgame;
		}

		public function getHideW()
		{
			return $this->hidew;
		}

		public function getAction()
		{
			return $this->action;
		}

		/*
		This function grabs a new random word and resets
		the SESSION variables back to default.
		
		return $tempArray which holds an array of '_'.			
		*/
		private function ranWord()
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
		private function basePicture()
		{
			$pic = glob('mod/hangman/img/' . "*.gif");
			$basepicture[] = array('PICTURE' => "<img src= $pic[0] />");
			return $basepicture;
		}

		/*
		This function determines which picture to
		choose from if the user guesses wrong.

		return $picturearray which holds the current
		hangman picture.
		*/
		private function picture($isletter)
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
		private function displayLetters($gletter, $word, $cletters)
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
		private function findingLetters($guess, $word)
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
		determining if the player won or lost.
		*/
		private function checkWinOrLoss($correctarray,$word)
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
		This function hides the letters if the player
		wins or loses the game.

		return true or false if the player wins or loses
		the game.
		*/
		private function hideWords($correctarray,$word)
		{
			if ($_SESSION['attempts'] == 0)
			{
				return true;
			}
			
			if (count($correctarray) == count(str_split($word)))
			{
				return true;
			}

			return false;
		}
	}

?>
