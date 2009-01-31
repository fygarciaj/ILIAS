<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
   +----------------------------------------------------------------------------+
*/

include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
* Multiple choice question GUI representation
*
* The assMultipleChoiceGUI class encapsulates the GUI representation
* for multiple choice questions.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assMultipleChoiceGUI extends assQuestionGUI
{
	var $choiceKeys;
	
	/**
	* assMultipleChoiceGUI constructor
	*
	* The constructor takes possible arguments an creates an instance of the assMultipleChoiceGUI object.
	*
	* @param integer $id The database id of a multiple choice question object
	* @access public
	*/
	function __construct($id = -1)
	{
		parent::__construct();
		include_once "./Modules/TestQuestionPool/classes/class.assMultipleChoice.php";
		$this->object = new assMultipleChoice();
		if ($id >= 0)
		{
			$this->object->loadFromDb($id);
		}
	}

	function getCommand($cmd)
	{
		if (substr($cmd, 0, 6) == "upload")
		{
			$cmd = "upload";
		}
		if (substr($cmd, 0, 11) == "deleteImage")
		{
			$cmd = "deleteImage";
		}
		return $cmd;
	}

	/**
	* Creates an output of the edit form for the question
	*
	* Creates an output of the edit form for the question
	*
	* @access public
	*/
	function editQuestion()
	{
		//$this->tpl->setVariable("HEADER", $this->object->getTitle());
		$this->tpl->addJavascript("./Services/JavaScript/js/Basic.js");
		$javascript = "<script type=\"text/javascript\">ilAddOnLoad(initialSelect);\n".
			"function initialSelect() {\n%s\n}</script>";
		$graphical_answer_setting = $this->object->getGraphicalAnswerSetting();
		$multiline_answers = $this->object->getMultilineAnswerSetting();
		if ($graphical_answer_setting == 0)
		{
			for ($i = 0; $i < $this->object->getAnswerCount(); $i++)
			{
				$answer = $this->object->getAnswer($i);
				if (strlen($answer->getImage())) $graphical_answer_setting = 1;
			}
		}
		$this->getQuestionTemplate();
		$this->tpl->addBlockFile("QUESTION_DATA", "question_data", "tpl.il_as_qpl_mc_mr.html", "Modules/TestQuestionPool");

		if ($this->object->getAnswerCount() > 0)
		{
			$this->tpl->setCurrentBlock("answersheading");
			$this->tpl->setVariable("TEXT_POINTS_CHECKED", $this->lng->txt("points_checked"));
			$this->tpl->setVariable("TEXT_POINTS_UNCHECKED", $this->lng->txt("points_unchecked"));
			$this->tpl->setVariable("TEXT_ANSWER_TEXT", $this->lng->txt("answer_text"));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("selectall");
			$this->tpl->setVariable("SELECT_ALL", $this->lng->txt("select_all"));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("existinganswers");
			$this->tpl->setVariable("DELETE", $this->lng->txt("delete"));
			$this->tpl->setVariable("MOVE", $this->lng->txt("move"));
			$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"".$this->lng->txt("arrow_downright")."\">");
			$this->tpl->parseCurrentBlock();
		}
		for ($i = 0; $i < $this->object->getAnswerCount(); $i++)
		{
			$answer = $this->object->getAnswer($i);
			if ($graphical_answer_setting == 1)
			{
				$imagefilename = $this->object->getImagePath() . $answer->getImage();
				if (!@file_exists($imagefilename))
				{
					$answer->setImage("");
				}
				if (strlen($answer->getImage()))
				{
					$imagepath = $this->object->getImagePathWeb() . $answer->getImage();
					$this->tpl->setCurrentBlock("graphical_answer_image");
					$this->tpl->setVariable("IMAGE_FILE", $imagepath);
					if (strlen($answer->getAnswertext()))
					{
						$this->tpl->setVariable("IMAGE_ALT", ilUtil::prepareFormOutput($answer->getAnswertext()));
					}
					else
					{
						$this->tpl->setVariable("IMAGE_ALT", $this->lng->txt("image"));
					}
					$this->tpl->setVariable("ANSWER_ORDER", $answer->getOrder());
					$this->tpl->setVariable("DELETE_IMAGE", $this->lng->txt("delete_image"));
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock("graphical_answer");
				$this->tpl->setVariable("ANSWER_ORDER", $answer->getOrder());
				$this->tpl->setVariable("UPLOAD_IMAGE", $this->lng->txt("upload_image"));
				$this->tpl->setVariable("VALUE_IMAGE", $answer->getImage());
				$this->tpl->parseCurrentBlock();
			}
			if ($multiline_answers)
			{
				$this->tpl->setCurrentBlock("show_textarea");
				$this->tpl->setVariable("ANSWER_ANSWER_ORDER", $answer->getOrder());
				$this->tpl->setVariable("VALUE_ANSWER", ilUtil::prepareFormOutput($answer->getAnswertext()));
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock("show_textinput");
				$this->tpl->setVariable("ANSWER_ANSWER_ORDER", $answer->getOrder());
				$this->tpl->setVariable("VALUE_ANSWER", ilUtil::prepareFormOutput($answer->getAnswertext()));
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("answers");
			$this->tpl->setVariable("VALUE_MULTIPLE_CHOICE_POINTS_CHECKED", $answer->getPoints());
			$this->tpl->setVariable("VALUE_MULTIPLE_CHOICE_POINTS_UNCHECKED", $answer->getPointsUnchecked());
			$this->tpl->setVariable("ANSWER_ORDER", $answer->getOrder());
			$this->tpl->parseCurrentBlock();
		}

		// call to other question data i.e. estimated working time block
		$this->outOtherQuestionData();

		$this->tpl->setCurrentBlock("HeadContent");

		if ($this->object->getAnswerCount() == 0)
		{
			$this->tpl->setVariable("CONTENT_BLOCK", sprintf($javascript, "document.frm_multiple_choice.title.focus();"));
		}
		else
		{
			switch ($this->ctrl->getCmd())
			{
				case "add":
					$nrOfAnswers = $_POST["nrOfAnswers"];
					if ((strcmp($nrOfAnswers, "yn") == 0) || (strcmp($nrOfAnswers, "tf") == 0)) $nrOfAnswers = 2;
					$this->tpl->setVariable("CONTENT_BLOCK", sprintf($javascript, "document.frm_multiple_choice.answer_".($this->object->getAnswerCount() - $nrOfAnswers).".focus(); document.getElementById('answer_".($this->object->getAnswerCount() - $nrOfAnswers)."').scrollIntoView(\"true\");"));
					break;
				case "deleteAnswer":
					if ($this->object->getAnswerCount() == 0)
					{
						$this->tpl->setVariable("CONTENT_BLOCK", sprintf($javascript, "document.frm_multiple_choice.title.focus();"));
					}
					else
					{
						$this->tpl->setVariable("CONTENT_BLOCK", sprintf($javascript, "document.frm_multiple_choice.answer_".($this->object->getAnswerCount() - 1).".focus(); document.getElementById('answer_".($this->object->getAnswerCount() - 1)."').scrollIntoView(\"true\");"));
					}
					break;
				default:
					$this->tpl->setVariable("CONTENT_BLOCK", sprintf($javascript, "document.frm_multiple_choice.title.focus();"));
					break;
			}
		}
		$this->tpl->parseCurrentBlock();
		
		for ($i = 1; $i < 10; $i++)
		{
			$this->tpl->setCurrentBlock("numbers");
			$this->tpl->setVariable("VALUE_NUMBER", $i);
			if ($i == 1)
			{
				$this->tpl->setVariable("TEXT_NUMBER", $i . " " . $this->lng->txt("answer"));
			}
			else
			{
				$this->tpl->setVariable("TEXT_NUMBER", $i . " " . $this->lng->txt("answers"));
			}
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setCurrentBlock("question_data");
		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TEXT_AUTHOR", $this->lng->txt("author"));
		$this->tpl->setVariable("TEXT_COMMENT", $this->lng->txt("description"));
		$this->tpl->setVariable("TEXT_QUESTION", $this->lng->txt("question"));
		$this->tpl->setVariable("MULTIPLE_CHOICE_ID", $this->object->getId());
		$this->tpl->setVariable("VALUE_MULTIPLE_CHOICE_TITLE", ilUtil::prepareFormOutput($this->object->getTitle()));
		$this->tpl->setVariable("VALUE_MULTIPLE_CHOICE_COMMENT", ilUtil::prepareFormOutput($this->object->getComment()));
		$this->tpl->setVariable("VALUE_MULTIPLE_CHOICE_AUTHOR", ilUtil::prepareFormOutput($this->object->getAuthor()));
		$this->tpl->setVariable("TEXT_GRAPHICAL_ANSWERS", $this->lng->txt("graphical_answers"));
		$this->tpl->setVariable("TEXT_HIDE_GRAPHICAL_ANSWER_SUPPORT", $this->lng->txt("graphical_answers_hide"));
		$this->tpl->setVariable("TEXT_SHOW_GRAPHICAL_ANSWER_SUPPORT", $this->lng->txt("graphical_answers_show"));
		if ($this->object->getGraphicalAnswerSetting() == 1)
		{
			$this->tpl->setVariable("SELECTED_SHOW_GRAPHICAL_ANSWER_SUPPORT", " selected=\"selected\"");
		}
		if ($multiline_answers)
		{
			$this->tpl->setVariable("SELECTED_SHOW_MULTILINE_ANSWERS", " selected=\"selected\"");
		}
		$this->tpl->setVariable("TEXT_HIDE_MULTILINE_ANSWERS", $this->lng->txt("multiline_answers_hide"));
		$this->tpl->setVariable("TEXT_SHOW_MULTILINE_ANSWERS", $this->lng->txt("multiline_answers_show"));
		$this->tpl->setVariable("SET_EDIT_MODE", $this->lng->txt("set_edit_mode"));
		$questiontext = $this->object->getQuestion();
		$this->tpl->setVariable("VALUE_QUESTION", ilUtil::prepareFormOutput($this->object->prepareTextareaOutput($questiontext)));
		$this->tpl->setVariable("VALUE_ADD_ANSWER", $this->lng->txt("add"));
		$this->tpl->setVariable("TEXT_SHUFFLE_ANSWERS", $this->lng->txt("shuffle_answers"));
		$this->tpl->setVariable("TXT_YES", $this->lng->txt("yes"));
		$this->tpl->setVariable("TXT_NO", $this->lng->txt("no"));
		if ($this->object->getShuffle())
		{
			$this->tpl->setVariable("SELECTED_YES", " selected=\"selected\"");
		}
		else
		{
			$this->tpl->setVariable("SELECTED_NO", " selected=\"selected\"");
		}
		$this->tpl->setVariable("SAVE",$this->lng->txt("save"));
		$this->tpl->setVariable("SAVE_EDIT", $this->lng->txt("save_edit"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
		$this->ctrl->setParameter($this, "sel_question_types", "assMultipleChoice");
		$this->tpl->setVariable("ACTION_MULTIPLE_CHOICE_TEST", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TEXT_QUESTION_TYPE", $this->outQuestionType());
		$this->tpl->parseCurrentBlock();

		include_once "./Services/RTE/classes/class.ilRTE.php";
		$rtestring = ilRTE::_getRTEClassname();
		include_once "./Services/RTE/classes/class.$rtestring.php";
		$rte = new $rtestring();
		$rte->addPlugin("latex");
		$rte->addButton("latex"); $rte->addButton("pastelatex");
		include_once "./classes/class.ilObject.php";
		$obj_id = $_GET["q_id"];
		$obj_type = ilObject::_lookupType($_GET["ref_id"], TRUE);
		$rte->addRTESupport($obj_id, $obj_type, "assessment");
		$this->tpl->setCurrentBlock("adm_content");
		//$this->tpl->setVariable("BODY_ATTRIBUTES", " onload=\"initialSelect();\""); 
		$this->tpl->parseCurrentBlock();
	}

	/**
	* add an answer
	*/
	function add()
	{
		//$this->setObjectData();
		$this->writePostData();

		if (!$this->checkInput())
		{
			ilUtil::sendInfo($this->lng->txt("fill_out_all_required_fields_add_answer"));
		}
		else
		{
			// add an answer template
			$nrOfAnswers = $_POST["nrOfAnswers"];
			switch ($nrOfAnswers)
			{
				case "tf":
					// add a true/false answer template
					$this->object->addAnswer(
						$this->lng->txt("true"),
						0,
						0,
						count($this->object->answers),
						""
					);
					$this->object->addAnswer(
						$this->lng->txt("false"),
						0,
						0,
						count($this->object->answers),
						""
					);
					break;
				case "yn":
					// add a yes/no answer template
					$this->object->addAnswer(
						$this->lng->txt("yes"),
						0,
						0,
						count($this->object->answers),
						""
					);
					$this->object->addAnswer(
						$this->lng->txt("no"),
						0,
						0,
						count($this->object->answers),
						""
					);
					break;
				default:
					for ($i = 0; $i < $nrOfAnswers; $i++)
					{
						$this->object->addAnswer(
							$this->lng->txt(""),
							0,
							0,
							count($this->object->answers),
							""
						);
					}
					break;
			}
		}

		$this->editQuestion();
	}

	/**
	* delete checked answers
	*/
	function deleteAnswer()
	{
		$this->writePostData();
		$answers = $_POST["chb_answers"];
		if (is_array($answers))
		{
			arsort($answers);
			foreach ($answers as $answer)
			{
				$this->object->deleteAnswer($answer);
			}
		}
		$this->editQuestion();
	}

	/**
	* check input fields
	*/
	function checkInput()
	{
		$cmd = $this->ctrl->getCmd();

		if ((!$_POST["title"]) or (!$_POST["author"]) or (!$_POST["question"]))
		{
//echo "<br>checkInput1:FALSE";
			return false;
		}
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/answer_(\d+)/", $key, $matches))
			{
				if (strlen($value) == 0)
				{
					if (strlen($_POST["uploaded_image_".$matches[1]]) == 0)
					{
						return false;
					}
				}
			}
		}

		return true;
	}

	/**
	* Evaluates a posted edit form and writes the form data in the question object
	*
	* Evaluates a posted edit form and writes the form data in the question object
	*
	* @return integer A positive value, if one of the required fields wasn't set, else 0
	* @access private
	*/
	function writePostData()
	{
//echo "here!"; exit;
//echo "<br>assMultipleChoiceGUI->writePostData()";
		$result = 0;
		if ((!$_POST["title"]) or (!$_POST["author"]) or (!$_POST["question"]))
		{
			$result = 1;
		}

		if (($result) and (($_POST["cmd"]["add"]) or ($_POST["cmd"]["add_tf"]) or ($_POST["cmd"]["add_yn"])))
		{
			// You cannot add answers before you enter the required data
			ilUtil::sendInfo($this->lng->txt("fill_out_all_required_fields_add_answer"));
			$_POST["cmd"]["add"] = "";
			$_POST["cmd"]["add_yn"] = "";
			$_POST["cmd"]["add_tf"] = "";
		}

		// Check the creation of new answer text fields
		if ($_POST["cmd"]["add"] or $_POST["cmd"]["add_yn"] or $_POST["cmd"]["add_tf"])
		{
			foreach ($_POST as $key => $value)
			{
				if (preg_match("/answer_(\d+)/", $key, $matches))
				{
					if (!$value)
					{
						$_POST["cmd"]["add"] = "";
						$_POST["cmd"]["add_yn"] = "";
						$_POST["cmd"]["add_tf"] = "";
						ilUtil::sendInfo($this->lng->txt("fill_out_all_answer_fields"));
					}
			 	}
			}
		}

		$this->object->setTitle(ilUtil::stripSlashes($_POST["title"]));
		$this->object->setAuthor(ilUtil::stripSlashes($_POST["author"]));
		$this->object->setComment(ilUtil::stripSlashes($_POST["comment"]));
		include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
		$questiontext = ilUtil::stripSlashes($_POST["question"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment"));
		$this->object->setQuestion($questiontext);
		$this->object->setShuffle($_POST["shuffle"]);

		$saved = $this->writeOtherPostData($result);
		$this->object->setMultilineAnswerSetting($_POST["multilineAnswers"]);
		$this->object->setGraphicalAnswerSetting($_POST["graphicalAnswerSupport"]);

		// Delete all existing answers and create new answers from the form data
		$this->object->flushAnswers();
		$graphical_answer_setting = $this->object->getGraphicalAnswerSetting();
		// Add all answers from the form into the object
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/answer_(\d+)/", $key, $matches))
			{
				$answer_image = $_POST["uploaded_image_".$matches[1]];
				if ($graphical_answer_setting == 1)
				{
					foreach ($_FILES as $key2 => $value2)
					{
						if (preg_match("/image_(\d+)/", $key2, $matches2))
						{
							if ($matches[1] == $matches2[1])
							{
								if ($value2["tmp_name"])
								{
									// upload the image
									if ($this->object->getId() <= 0)
									{
										$this->object->saveToDb();
										$saved = true;
										$this->error .= $this->lng->txt("question_saved_for_upload") . "<br />";
									}
									$value2['name'] = $this->object->createNewImageFileName($value2['name']);
									$upload_result = $this->object->setImageFile($value2['name'], $value2['tmp_name']);
									switch ($upload_result)
									{
										case 0:
											$_POST["image_".$matches2[1]] = $value2['name'];
											$answer_image = $value2['name'];
											break;
										case 1:
											$this->error .= $this->lng->txt("error_image_upload_wrong_format") . "<br />";
											break;
										case 2:
											$this->error .= $this->lng->txt("error_image_upload_copy_file") . "<br />";
											break;
									}
								}
							}
						}
					}
				}
				$points = $_POST["points_checked_$matches[1]"];
				$points_unchecked = $_POST["points_unchecked_$matches[1]"];
				$answertext = ilUtil::stripSlashes($_POST["$key"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment"));
				$this->object->addAnswer(
					$answertext,
					ilUtil::stripSlashes($points),
					ilUtil::stripSlashes($points_unchecked),
					ilUtil::stripSlashes($matches[1]),
					$answer_image
				);
			}
		}

		if ($this->object->getMaximumPoints() < 0)
		{
			$result = 1;
			$this->setErrorMessage($this->lng->txt("enter_enough_positive_points"));
		}
		
		// Set the question id from a hidden form parameter
		if ($_POST["multiple_choice_id"] > 0)
		{
			$this->object->setId($_POST["multiple_choice_id"]);
		}
		
		if ($saved)
		{
			// If the question was saved automatically before an upload, we have to make
			// sure, that the state after the upload is saved. Otherwise the user could be
			// irritated, if he presses cancel, because he only has the question state before
			// the upload process.
			$this->object->saveToDb();
			$this->ctrl->setParameter($this, "q_id", $this->object->getId());
		}

		return $result;
	}
	
	function outQuestionForTest($formaction, $active_id, $pass = NULL, $is_postponed = FALSE, $use_post_solutions = FALSE, $show_feedback = FALSE)
	{
		$test_output = $this->getTestOutput($active_id, $pass, $is_postponed, $use_post_solutions, $show_feedback); 
		$this->tpl->setVariable("QUESTION_OUTPUT", $test_output);
		$this->tpl->setVariable("FORMACTION", $formaction);
	}

	function getSolutionOutput($active_id, $pass = NULL, $graphicalOutput = FALSE, $result_output = FALSE, $show_question_only = TRUE, $show_feedback = FALSE, $show_correct_solution = FALSE)
	{
		// shuffle output
		$keys = $this->getChoiceKeys();

		// get the solution of the user for the active pass or from the last pass if allowed
		$user_solution = array();
		if (($active_id > 0) && (!$show_correct_solution))
		{
			$solutions =& $this->object->getSolutionValues($active_id, $pass);
			foreach ($solutions as $idx => $solution_value)
			{
				array_push($user_solution, $solution_value["value1"]);
			}
		}
		else
		{
			// take the correct solution instead of the user solution
			foreach ($this->object->answers as $index => $answer)
			{
				$points_checked = $answer->getPointsChecked();
				$points_unchecked = $answer->getPointsUnchecked();
				if ($points_checked > $points_unchecked)
				{
					if ($points_checked > 0)
					{
						array_push($user_solution, $index);
					}
				}
			}
		}
		
		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_mc_mr_output_solution.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html",TRUE, TRUE, "Modules/TestQuestionPool");
		foreach ($keys as $answer_id)
		{
			$answer = $this->object->answers[$answer_id];
			if (($active_id > 0) && (!$show_correct_solution))
			{
				if ($graphicalOutput)
				{
					// output of ok/not ok icons for user entered solutions
					$ok = FALSE;
					$checked = FALSE;
					foreach ($user_solution as $mc_solution)
					{
						if (strcmp($mc_solution, $answer_id) == 0)
						{
							$checked = TRUE;
						}
					}
					if ($checked)
					{
						if ($answer->getPointsChecked() > $answer->getPointsUnchecked())
						{
							$ok = TRUE;
						}
						else
						{
							$ok = FALSE;
						}
					}
					else
					{
						if ($answer->getPointsChecked() > $answer->getPointsUnchecked())
						{
							$ok = FALSE;
						}
						else
						{
							$ok = TRUE;
						}
					}
					if ($ok)
					{
						$template->setCurrentBlock("icon_ok");
						$template->setVariable("ICON_OK", ilUtil::getImagePath("icon_ok.gif"));
						$template->setVariable("TEXT_OK", $this->lng->txt("answer_is_right"));
						$template->parseCurrentBlock();
					}
					else
					{
						$template->setCurrentBlock("icon_ok");
						$template->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_not_ok.gif"));
						$template->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_wrong"));
						$template->parseCurrentBlock();
					}
				}
			}
			if (strlen($answer->getImage()))
			{
				$template->setCurrentBlock("answer_image");
				$template->setVariable("ANSWER_IMAGE_URL", $this->object->getImagePathWeb() . $answer->getImage());
				list($width, $height, $type, $attr) = getimagesize($this->object->getImagePath() . $answer->getImage());
				$alt = $answer->getImage();
				if (strlen($answer->getAnswertext()))
				{
					$alt = $answer->getAnswertext();
				}
				$alt = preg_replace("/<[^>]*?>/", "", $alt);
				$template->setVariable("ATTR", $attr);
				$template->setVariable("ANSWER_IMAGE_ALT", ilUtil::prepareFormOutput($alt));
				$template->setVariable("ANSWER_IMAGE_TITLE", ilUtil::prepareFormOutput($alt));
				$template->parseCurrentBlock();
			}
			if ($show_feedback)
			{
				foreach ($user_solution as $mc_solution)
				{
					if (strcmp($mc_solution, $answer_id) == 0)
					{
						$fb = $this->object->getFeedbackSingleAnswer($answer_id);
						if (strlen($fb))
						{
							$template->setCurrentBlock("feedback");
							$template->setVariable("FEEDBACK", $fb);
							$template->parseCurrentBlock();
						}
					}
				}
			}
			$template->setCurrentBlock("answer_row");
			$template->setVariable("ANSWER_TEXT", $this->object->prepareTextareaOutput($answer->getAnswertext(), TRUE));
			$checked = FALSE;
			if ($result_output)
			{
				$pointschecked = $this->object->answers[$answer_id]->getPointsChecked();
				$pointsunchecked = $this->object->answers[$answer_id]->getPointsUnchecked();
				$resulttextchecked = ($pointschecked == 1) || ($pointschecked == -1) ? "%s " . $this->lng->txt("point") : "%s " . $this->lng->txt("points");
				$resulttextunchecked = ($pointsunchecked == 1) || ($pointsunchecked == -1) ? "%s " . $this->lng->txt("point") : "%s " . $this->lng->txt("points"); 
				$template->setVariable("RESULT_OUTPUT", sprintf("(" . $this->lng->txt("checkbox_checked") . " = $resulttextchecked, " . $this->lng->txt("checkbox_unchecked") . " = $resulttextunchecked)", $pointschecked, $pointsunchecked));
			}
			foreach ($user_solution as $mc_solution)
			{
				if (strcmp($mc_solution, $answer_id) == 0)
				{
					$template->setVariable("SOLUTION_IMAGE", ilUtil::getHtmlPath(ilUtil::getImagePath("checkbox_checked.gif")));
					$template->setVariable("SOLUTION_ALT", $this->lng->txt("checked"));
					$checked = TRUE;
				}
			}
			if (!$checked)
			{
				$template->setVariable("SOLUTION_IMAGE", ilUtil::getHtmlPath(ilUtil::getImagePath("checkbox_unchecked.gif")));
				$template->setVariable("SOLUTION_ALT", $this->lng->txt("unchecked"));
			}
			$template->parseCurrentBlock();
		}
		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$questionoutput = $template->get();
		$feedback = ($show_feedback) ? $this->getAnswerFeedbackOutput($active_id, $pass) : "";
		if (strlen($feedback)) $solutiontemplate->setVariable("FEEDBACK", $feedback);
		$solutiontemplate->setVariable("SOLUTION_OUTPUT", $questionoutput);

		$solutionoutput = $solutiontemplate->get(); 
		if (!$show_question_only)
		{
			// get page object output
			$solutionoutput = $this->getILIASPage($solutionoutput);
		}
		return $solutionoutput;
	}
	
	function getPreview($show_question_only = FALSE)
	{
		// shuffle output
		$keys = $this->getChoiceKeys();

		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_mc_mr_output.html", TRUE, TRUE, "Modules/TestQuestionPool");
		foreach ($keys as $answer_id)
		{
			$answer = $this->object->answers[$answer_id];
			if (strlen($answer->getImage()))
			{
				$template->setCurrentBlock("answer_image");
				$template->setVariable("ANSWER_IMAGE_URL", $this->object->getImagePathWeb() . $answer->getImage());
				$alt = $answer->getImage();
				if (strlen($answer->getAnswertext()))
				{
					$alt = $answer->getAnswertext();
				}
				$alt = preg_replace("/<[^>]*?>/", "", $alt);
				$template->setVariable("ANSWER_IMAGE_ALT", ilUtil::prepareFormOutput($alt));
				$template->setVariable("ANSWER_IMAGE_TITLE", ilUtil::prepareFormOutput($alt));
				$template->parseCurrentBlock();
			}
			$template->setCurrentBlock("answer_row");
			$template->setVariable("ANSWER_ID", $answer_id);
			$template->setVariable("ANSWER_TEXT", $this->object->prepareTextareaOutput($answer->getAnswertext(), TRUE));
			$template->parseCurrentBlock();
		}
		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$questionoutput = $template->get();
		if (!$show_question_only)
		{
			// get page object output
			$questionoutput = $this->getILIASPage($questionoutput);
		}
		return $questionoutput;
	}

	function getTestOutput($active_id, $pass = NULL, $is_postponed = FALSE, $use_post_solutions = FALSE, $show_feedback = FALSE)
	{
		// shuffle output
		$keys = $this->getChoiceKeys();

		// get the solution of the user for the active pass or from the last pass if allowed
		$user_solution = array();
		if ($active_id)
		{
			$solutions = NULL;
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			if (!ilObjTest::_getUsePreviousAnswers($active_id, true))
			{
				if (is_null($pass)) $pass = ilObjTest::_getPass($active_id);
			}
			$solutions =& $this->object->getSolutionValues($active_id, $pass);
			foreach ($solutions as $idx => $solution_value)
			{
				array_push($user_solution, $solution_value["value1"]);
			}
		}
		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_mc_mr_output.html", TRUE, TRUE, "Modules/TestQuestionPool");
		foreach ($keys as $answer_id)
		{
			$answer = $this->object->answers[$answer_id];
			if (strlen($answer->getImage()))
			{
				$template->setCurrentBlock("answer_image");
				$template->setVariable("ANSWER_IMAGE_URL", $this->object->getImagePathWeb() . $answer->getImage());
				$alt = $answer->getImage();
				if (strlen($answer->getAnswertext()))
				{
					$alt = $answer->getAnswertext();
				}
				$alt = preg_replace("/<[^>]*?>/", "", $alt);
				$template->setVariable("ANSWER_IMAGE_ALT", ilUtil::prepareFormOutput($alt));
				$template->setVariable("ANSWER_IMAGE_TITLE", ilUtil::prepareFormOutput($alt));
				$template->parseCurrentBlock();
			}

			foreach ($user_solution as $mc_solution)
			{
				if (strcmp($mc_solution, $answer_id) == 0)
				{
					if ($show_feedback)
					{
						$feedback = $this->object->getFeedbackSingleAnswer($answer_id);
						if (strlen($feedback))
						{
							$template->setCurrentBlock("feedback");
							$template->setVariable("FEEDBACK", $feedback);
							$template->parseCurrentBlock();
						}
					}
				}
			}

			$template->setCurrentBlock("answer_row");
			$template->setVariable("ANSWER_ID", $answer_id);
			$template->setVariable("ANSWER_TEXT", $this->object->prepareTextareaOutput($answer->getAnswertext(), TRUE));
			foreach ($user_solution as $mc_solution)
			{
				if (strcmp($mc_solution, $answer_id) == 0)
				{
					$template->setVariable("CHECKED_ANSWER", " checked=\"checked\"");
				}
			}
			$template->parseCurrentBlock();
		}
		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$questionoutput = $template->get();
		$pageoutput = $this->outQuestionPage("", $is_postponed, $active_id, $questionoutput);
		return $pageoutput;
	}

	/**
	* upload an image
	*/
	function upload()
	{
		$this->writePostData();
		$this->editQuestion();
	}
	
	function deleteImage()
	{
		if ($this->writePostData())
		{
			ilUtil::sendInfo($this->getErrorMessage());
			$this->editQuestion();
			return;
		}
		$imageorder = "";
		foreach ($_POST["cmd"] as $key => $value)
		{
			if (preg_match("/deleteImage_(\d+)/", $key, $matches))
			{
				$imageorder = $matches[1];
			}
		}
		for ($i = 0; $i < $this->object->getAnswerCount(); $i++)
		{
			$answer = $this->object->getAnswer($i);
			if ($answer->getOrder() == $imageorder)
			{
				$this->object->deleteImage($answer->getImage());
				$this->object->answers[$i]->setImage("");
			}
		}
		$this->editQuestion();
	}

	function editMode()
	{
		global $ilUser;
		
		$this->object->setMultilineAnswerSetting($_POST["multilineAnswers"]);
		$this->object->setGraphicalAnswerSetting($_POST["graphicalAnswerSupport"]);
		$this->writePostData();
		$this->editQuestion();
	}
	
	/**
	* Saves the feedback for a single choice question
	*
	* Saves the feedback for a single choice question
	*
	* @access public
	*/
	function saveFeedback()
	{
		include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
		$this->object->saveFeedbackGeneric(0, ilUtil::stripSlashes($_POST["feedback_incomplete"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment")));
		$this->object->saveFeedbackGeneric(1, ilUtil::stripSlashes($_POST["feedback_complete"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment")));
		foreach ($this->object->answers as $index => $answer)
		{
			$this->object->saveFeedbackSingleAnswer($index, ilUtil::stripSlashes($_POST["feedback_answer_$index"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment")));
		}
		$this->object->cleanupMediaObjectUsage();
		parent::saveFeedback();
	}

	/**
	* Creates the output of the feedback page for a single choice question
	*
	* Creates the output of the feedback page for a single choice question
	*
	* @access public
	*/
	function feedback()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "feedback", "tpl.il_as_qpl_mc_mr_feedback.html", "Modules/TestQuestionPool");
		foreach ($this->object->answers as $index => $answer)
		{
			$this->tpl->setCurrentBlock("feedback_answer");
			$this->tpl->setVariable("FEEDBACK_TEXT_ANSWER", $this->lng->txt("feedback"));
			$this->tpl->setVariable("ANSWER_TEXT", $this->object->prepareTextareaOutput($answer->getAnswertext(), TRUE));
			$this->tpl->setVariable("ANSWER_ID", $index);
			$this->tpl->setVariable("VALUE_FEEDBACK_ANSWER", ilUtil::prepareFormOutput($this->object->prepareTextareaOutput($this->object->getFeedbackSingleAnswer($index)), FALSE));
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setVariable("FEEDBACK_TEXT", $this->lng->txt("feedback"));
		$this->tpl->setVariable("FEEDBACK_COMPLETE", $this->lng->txt("feedback_complete_solution"));
		$this->tpl->setVariable("VALUE_FEEDBACK_COMPLETE", ilUtil::prepareFormOutput($this->object->prepareTextareaOutput($this->object->getFeedbackGeneric(1)), FALSE));
		$this->tpl->setVariable("FEEDBACK_INCOMPLETE", $this->lng->txt("feedback_incomplete_solution"));
		$this->tpl->setVariable("VALUE_FEEDBACK_INCOMPLETE", ilUtil::prepareFormOutput($this->object->prepareTextareaOutput($this->object->getFeedbackGeneric(0)), FALSE));
		$this->tpl->setVariable("FEEDBACK_ANSWERS", $this->lng->txt("feedback_answers"));
		$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		include_once "./Services/RTE/classes/class.ilRTE.php";
		$rtestring = ilRTE::_getRTEClassname();
		include_once "./Services/RTE/classes/class.$rtestring.php";
		$rte = new $rtestring();
		$rte->addPlugin("latex");
		$rte->addButton("latex"); $rte->addButton("pastelatex");
		include_once "./classes/class.ilObject.php";
		$obj_id = $_GET["q_id"];
		$obj_type = ilObject::_lookupType($_GET["ref_id"], TRUE);
		$rte->addRTESupport($obj_id, $obj_type, "assessment");
	}

	/**
	* Sets the ILIAS tabs for this question type
	*
	* Sets the ILIAS tabs for this question type
	*
	* @access public
	*/
	function setQuestionTabs()
	{
		global $rbacsystem, $ilTabs;
		
		$this->ctrl->setParameterByClass("ilpageobjectgui", "q_id", $_GET["q_id"]);
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		$q_type = $this->object->getQuestionType();

		if (strlen($q_type))
		{
			$classname = $q_type . "GUI";
			$this->ctrl->setParameterByClass(strtolower($classname), "sel_question_types", $q_type);
			$this->ctrl->setParameterByClass(strtolower($classname), "q_id", $_GET["q_id"]);
		}

		if ($_GET["q_id"])
		{
			if ($rbacsystem->checkAccess('write', $_GET["ref_id"]))
			{
				// edit page
				$ilTabs->addTarget("edit_content",
					$this->ctrl->getLinkTargetByClass("ilPageObjectGUI", "edit"),
					array("edit", "insert", "exec_pg"),
					"", "", $force_active);
			}
	
			// edit page
			$ilTabs->addTarget("preview",
				$this->ctrl->getLinkTargetByClass("ilPageObjectGUI", "preview"),
				array("preview"),
				"ilPageObjectGUI", "", $force_active);
		}
		$force_active = false;
		if ($rbacsystem->checkAccess('write', $_GET["ref_id"]))
		{
			$url = "";
			if ($classname) $url = $this->ctrl->getLinkTargetByClass($classname, "editQuestion");
			$force_active = false;
			$commands = $_POST["cmd"];
			if (is_array($commands))
			{
				foreach ($commands as $key => $value)
				{
					if (preg_match("/^deleteImage_.*/", $key, $matches) || 
						preg_match("/^upload_.*/", $key, $matches)
						)
					{
						$force_active = true;
					}
				}
			}
			// edit question properties
			$ilTabs->addTarget("edit_properties",
				$url,
				array("editQuestion", "save", "cancel",
					"toggleGraphicalAnswers", "setMediaMode", "uploadingImage", "add", "editMode", "deleteAnswer",
					"saveEdit"),
				$classname, "", $force_active);
		}

		if ($_GET["q_id"])
		{
			$ilTabs->addTarget("feedback",
				$this->ctrl->getLinkTargetByClass($classname, "feedback"),
				array("feedback", "saveFeedback"),
				$classname, "");
		}
		
		if ($_GET["q_id"])
		{
			$ilTabs->addTarget("solution_hint",
				$this->ctrl->getLinkTargetByClass($classname, "suggestedsolution"),
				array("suggestedsolution", "saveSuggestedSolution", "outSolutionExplorer", "cancel", 
				"addSuggestedSolution","cancelExplorer", "linkChilds", "removeSuggestedSolution"
				),
				$classname, 
				""
			);
		}

		// Assessment of questions sub menu entry
		if ($_GET["q_id"])
		{
			$ilTabs->addTarget("statistics",
				$this->ctrl->getLinkTargetByClass($classname, "assessment"),
				array("assessment"),
				$classname, "");
		}
		
		if (($_GET["calling_test"] > 0) || ($_GET["test_ref_id"] > 0))
		{
			$ref_id = $_GET["calling_test"];
			if (strlen($ref_id) == 0) $ref_id = $_GET["test_ref_id"];
			$ilTabs->setBackTarget($this->lng->txt("backtocallingtest"), "ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=$ref_id");
		}
		else
		{
			$ilTabs->setBackTarget($this->lng->txt("qpl"), $this->ctrl->getLinkTargetByClass("ilobjquestionpoolgui", "questions"));
		}
	}
	
	/*
	 * Create the key index numbers for the array of choices
	 * 
	 * @return array
	 */
	function getChoiceKeys()
	{
		if (strcmp($_GET["activecommand"], "directfeedback") == 0)
		{
			if (is_array($_SESSION["choicekeys"])) $this->choiceKeys = $_SESSION["choicekeys"];
		}
		if (!is_array($this->choiceKeys))
		{
			$this->choiceKeys = array_keys($this->object->answers);
			if ($this->object->getShuffle())
			{
				$this->choiceKeys = $this->object->pcArrayShuffle($this->choiceKeys);
			}
		}
		$_SESSION["choicekeys"] = $this->choiceKeys;
		return $this->choiceKeys;
	}
}
?>
