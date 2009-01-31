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
* The assFlashQuestionGUI class encapsulates the GUI representation
* for Mathematik Online based questions.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
* @ilctrl_iscalledby assFlashQuestionGUI: ilObjQuestionPoolGUI
* */
class assFlashQuestionGUI extends assQuestionGUI
{
	private $newUnitId;
	
	/**
	* assFlashQuestionGUI constructor
	*
	* The constructor takes possible arguments an creates an instance of the assFlashQuestionGUI object.
	*
	* @param integer $id The database id of a single choice question object
	* @access public
	*/
	function __construct($id = -1)
	{
		parent::__construct();
		include_once "./Modules/TestQuestionPool/classes/class.assFlashQuestion.php";
		$this->object = new assFlashQuestion();
		$this->newUnitId = null;
		if ($id >= 0)
		{
			$this->object->loadFromDb($id);
		}
	}

	function getCommand($cmd)
	{
		if (preg_match("/suggestrange_(.*?)/", $cmd, $matches))
		{
			$cmd = "suggestRange";
		}
		return $cmd;
	}

	/**
	* Suggest a range for a result
	*
	* @access public
	*/
	function suggestRange()
	{
		if ($this->writePostData())
		{
			ilUtil::sendInfo($this->getErrorMessage());
		}
		$this->editQuestion();
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
		$save = ((strcmp($this->ctrl->getCmd(), "save") == 0) || (strcmp($this->ctrl->getCmd(), "saveEdit") == 0)) ? TRUE : FALSE;
		$this->tpl->addJavascript("./Services/JavaScript/js/Basic.js");
		$this->getQuestionTemplate();

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt("assFlashQuestion"));
		$form->setMultipart(FALSE);
		$form->setTableWidth("100%");
		$form->setId("flash");

		// title
		$title = new ilTextInputGUI($this->lng->txt("title"), "title");
		$title->setValue($this->object->getTitle());
		$title->setRequired(TRUE);
		$form->addItem($title);
		// author
		$author = new ilTextInputGUI($this->lng->txt("author"), "author");
		$author->setValue($this->object->getAuthor());
		$author->setRequired(TRUE);
		$form->addItem($author);
		// description
		$description = new ilTextInputGUI($this->lng->txt("description"), "comment");
		$description->setValue($this->object->getComment());
		$description->setRequired(FALSE);
		$form->addItem($description);
		// questiontext
		$question = new ilTextAreaInputGUI($this->lng->txt("question"), "question");
		$question->setValue($this->object->prepareTextareaOutput($this->object->getQuestion()));
		$question->setRequired(TRUE);
		$question->setRows(10);
		$question->setCols(80);
		$question->setUseRte(TRUE);
		$question->addPlugin("latex");
		$question->addButton("latex");
		$question->setRTESupport($this->object->getId(), "qpl", "assessment");
		$form->addItem($question);
		// duration
		$duration = new ilDurationInputGUI($this->lng->txt("working_time"), "Estimated");
		$duration->setShowHours(TRUE);
		$duration->setShowMinutes(TRUE);
		$duration->setShowSeconds(TRUE);
		$ewt = $this->object->getEstimatedWorkingTime();
		$duration->setHours($ewt["h"]);
		$duration->setMinutes($ewt["m"]);
		$duration->setSeconds($ewt["s"]);
		$duration->setRequired(FALSE);
		$form->addItem($duration);
		
		// flash file
		$flash = new ilFlashFileInputGUI($this->lng->txt("flashfile"), "flash");
		$flash->setRequired(TRUE);
		if ($_SESSION["flash_upload_filename"])
		{
			$flash->setApplet($_SESSION["flash_upload_filename"]);
		}
		else
		{
			if (strlen($this->object->getApplet()))
			{
				$flash->setApplet($this->object->getFlashPathWeb() . $this->object->getApplet());
			}
		}
		$flash->setWidth($this->object->getWidth());
		$flash->setHeight($this->object->getHeight());
		$flash->setParameters($this->object->getParameters());
		$form->addItem($flash);
		if ($this->object->getId())
		{
			$hidden = new ilHiddenInputGUI("", "ID");
			$hidden->setValue($this->object->getId());
			$form->addItem($hidden);
		}
		// points
		$points = new ilNumberInputGUI($this->lng->txt("points"), "points");
		$points->setValue($this->object->getPoints());
		$points->setRequired(TRUE);
		$points->setSize(3);
		$points->setMinValue(0.0);
		$form->addItem($points);

		$form->addCommandButton("save", $this->lng->txt("save"));
		$form->addCommandButton("saveEdit", $this->lng->txt("save_edit"));
		$form->addCommandButton("cancel", $this->lng->txt("cancel"));
		
		if ($save)
		{
			$form->checkInput();
		}
		
		$this->tpl->setVariable("QUESTION_DATA", $form->getHTML());
	}
	
	public function parseQuestion()
	{
		$this->writePostData();
		$this->editQuestion();
	}

	/**
	* check input fields
	*/
	function checkInput()
	{
		$cmd = $this->ctrl->getCmd();

		if ((!$_POST["title"]) or (!$_POST["author"]) or (!$_POST["question"]) or (!strlen($_POST["points"])))
		{
			$this->addErrorMessage($this->lng->txt("fill_out_all_required_fields"));
			return FALSE;
		}
		
		
		return TRUE;
	}

	function flashAddParam()
	{
		$this->writePostData();
		$this->object->addParameter("", "");
		$this->editQuestion();
	}

	/**
	* Evaluates a posted edit form and writes the form data in the question object
	*
	* @return integer A positive value, if one of the required fields wasn't set, else 0
	* @access private
	*/
	function writePostData()
	{
		global $ilLog;
		$this->setErrorMessage("");
		if ($_POST["flash_delete"] == 1)
		{
			$this->object->deleteApplet();
		}
		if ($_FILES["flash"]["tmp_name"])
		{
			if ($_SESSION["flash_upload_filename"]) @unlink($_SESSION["flash_upload_filename"]);
			$filename = $this->object->moveUploadedMediaFile($_FILES["flash"]["tmp_name"], $_FILES["flash"]["name"]);
			if ($filename) $_SESSION["flash_upload_filename"] = $filename;
			$this->object->setApplet($_FILES["flash"]["name"]);
		}
		else if ($_SESSION["flash_upload_filename"])
		{
			if (@file_exists($_SESSION["flash_upload_filename"]))
			{
				$filename = basename($_SESSION["flash_upload_filename"]);
				if (preg_match("/(.*?)____.*/", $filename, $matches))
				{
					$this->object->setApplet($matches[1]);
				}
				else
				{
					unset($_SESSION["flash_upload_filename"]);
				}
			}
			else
			{
				unset($_SESSION["flash_upload_filename"]);
			}
		}
		$this->object->clearParameters();
		if (is_array($_POST["flash_flash_param_name"]))
		{
			foreach ($_POST["flash_flash_param_name"] as $key => $value)
			{
				if ($_POST["flash_flash_param_delete"][$key] != 1)
				{
					$this->object->addParameter($value, $_POST["flash_flash_param_value"][$key]);
				}
			}
		}
		$checked = $this->checkInput();
		$this->object->setTitle(ilUtil::stripSlashes($_POST["title"]));
		$this->object->setAuthor(ilUtil::stripSlashes($_POST["author"]));
		$this->object->setComment(ilUtil::stripSlashes($_POST["comment"]));
		include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
		$formtags = ",input,select,option,button";
		$questiontext = ilUtil::stripSlashes($_POST["question"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment") . $formtags);
		$this->object->setQuestion($questiontext);
		$this->object->setEstimatedWorkingTime(
			ilUtil::stripSlashes($_POST["Estimated"]["hh"]),
			ilUtil::stripSlashes($_POST["Estimated"]["mm"]),
			ilUtil::stripSlashes($_POST["Estimated"]["ss"])
		);
		$this->object->setWidth($_POST["flash_width"]);
		$this->object->setHeight($_POST["flash_height"]);
		$this->object->setPoints($_POST["points"]);

		// Set the question id from a hidden form parameter
		if ($_POST["id"] > 0)
		{
			$this->object->setId($_POST["id"]);
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
		return ($checked) ? 0 : 1;
	}

	function outQuestionForTest($formaction, $active_id, $pass = NULL, $is_postponed = FALSE, $use_post_solutions = FALSE, $show_feedback = FALSE)
	{
		$test_output = $this->getTestOutput($active_id, $pass, $is_postponed, $use_post_solutions, $show_feedback); 
		$this->tpl->setVariable("QUESTION_OUTPUT", $test_output);
		$this->tpl->setVariable("FORMACTION", $formaction);
	}

	function getSolutionOutput($active_id, $pass = NULL, $graphicalOutput = FALSE, $result_output = FALSE, $show_question_only = TRUE, $show_feedback = FALSE, $show_correct_solution = FALSE)
	{
		// get the solution of the user for the active pass or from the last pass if allowed
		$template = new ilTemplate("tpl.il_as_qpl_flash_question_output_solution.html", TRUE, TRUE, "Modules/TestQuestionPool");

		$params = array();
		if (is_array($this->object->getParameters()))
		{
			foreach ($this->object->getParameters() as $name => $value)
			{
				array_push($params, urlencode($name) . "=" . urlencode($value));
			}
		}

		array_push($params, "session_id=" . urlencode($_COOKIE["PHPSESSID"]));
		array_push($params, "client=" . urlencode(CLIENT_ID));
		array_push($params, "points_max=" . urlencode($this->object->getPoints()));
		array_push($params, "server=" . urlencode(ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH) . "/webservice/soap/server.php?wsdl"));
		if (!is_null($pass))
		{
			array_push($params, "pass=" . $pass);
		}
		else
		{
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			array_push($params, "pass=" . ilObjTest::_getPass($active_id));
		}
		if ($active_id)
		{
			array_push($params, "active_id=" . $active_id);
		}
		array_push($params, "question_id=" . $this->object->getId());

		if ($show_correct_solution)
		{

		}

		if (($active_id > 0) && (!$show_correct_solution))
		{
			if ($graphicalOutput)
			{
				// output of ok/not ok icons for user entered solutions
				$reached_points = $this->object->getReachedPoints($active_id, $pass);
				if ($reached_points == $this->object->getMaximumPoints())
				{
					$template->setCurrentBlock("icon_ok");
					$template->setVariable("ICON_OK", ilUtil::getImagePath("icon_ok.gif"));
					$template->setVariable("TEXT_OK", $this->lng->txt("answer_is_right"));
					$template->parseCurrentBlock();
				}
				else
				{
					$template->setCurrentBlock("icon_ok");
					if ($reached_points > 0)
					{
						$template->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_mostly_ok.gif"));
						$template->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_not_correct_but_positive"));
					}
					else
					{
						$template->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_not_ok.gif"));
						$template->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_wrong"));
					}
					$template->parseCurrentBlock();
				}
			}
		}

		if (count($params))
		{
			$template->setCurrentBlock("flash_vars");
			$template->setVariable("FLASH_VARS", join($params, "&"));
			$template->parseCurrentBlock();
			$template->setCurrentBlock("applet_parameters");
			$template->setVariable("PARAM_VALUE", join($params, "&"));
			$template->parseCurrentBlock();
		}
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($this->object->getQuestion(), TRUE));
		$template->setVariable("APPLET_WIDTH", $this->object->getWidth());
		$template->setVariable("APPLET_HEIGHT", $this->object->getHeight());
		$template->setVariable("ID", $this->object->getId());
		$template->setVariable("APPLET_PATH", $this->object->getFlashPathWeb() . $this->object->getApplet());
		$template->setVariable("APPLET_FILE", $this->object->getApplet());

		$questionoutput = $template->get();
		$solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html",TRUE, TRUE, "Modules/TestQuestionPool");
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
		$template = new ilTemplate("tpl.il_as_qpl_flash_question_output.html",TRUE, TRUE, "Modules/TestQuestionPool");
		$params = array();
		if (is_array($this->object->getParameters()))
		{
			foreach ($this->object->getParameters() as $name => $value)
			{
				array_push($params, urlencode($name) . "=" . urlencode($value));
			}
		}
		if (count($params))
		{
			$template->setCurrentBlock("flash_vars");
			$template->setVariable("FLASH_VARS", join($params, "&"));
			$template->parseCurrentBlock();
			$template->setCurrentBlock("applet_parameters");
			$template->setVariable("PARAM_VALUE", join($params, "&"));
			$template->parseCurrentBlock();
		}
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($this->object->getQuestion(), TRUE));
		$template->setVariable("APPLET_WIDTH", $this->object->getWidth());
		$template->setVariable("APPLET_HEIGHT", $this->object->getHeight());
		$template->setVariable("ID", $this->object->getId());
		$template->setVariable("APPLET_PATH", $this->object->getFlashPathWeb() . $this->object->getApplet());
		$template->setVariable("APPLET_FILE", $this->object->getApplet());
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
		// generate the question output
		$template = new ilTemplate("tpl.il_as_qpl_flash_question_output.html",TRUE, TRUE, "Modules/TestQuestionPool");
		$params = array();
		if (is_array($this->object->getParameters()))
		{
			foreach ($this->object->getParameters() as $name => $value)
			{
				array_push($params, urlencode($name) . "=" . urlencode($value));
			}
		}

		array_push($params, "session_id=" . urlencode($_COOKIE["PHPSESSID"]));
		array_push($params, "client=" . urlencode(CLIENT_ID));
		array_push($params, "points_max=" . urlencode($this->object->getPoints()));
		array_push($params, "server=" . urlencode(ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH) . "/webservice/soap/server.php?wsdl"));
		if (strlen($pass))
		{
			array_push($params, "pass=" . $pass);
		}
		else
		{
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			array_push($params, "pass=" . ilObjTest::_getPass($active_id));
		}
		if ($active_id)
		{
			array_push($params, "active_id=" . $active_id);
		}
		array_push($params, "question_id=" . $this->object->getId());

		if (count($params))
		{
			$template->setCurrentBlock("flash_vars");
			$template->setVariable("FLASH_VARS", join($params, "&"));
			$template->parseCurrentBlock();
			$template->setCurrentBlock("applet_parameters");
			$template->setVariable("PARAM_VALUE", join($params, "&"));
			$template->parseCurrentBlock();
		}
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($this->object->getQuestion(), TRUE));
		$template->setVariable("APPLET_WIDTH", $this->object->getWidth());
		$template->setVariable("APPLET_HEIGHT", $this->object->getHeight());
		$template->setVariable("ID", $this->object->getId());
		$template->setVariable("APPLET_PATH", $this->object->getFlashPathWeb() . $this->object->getApplet());
		$template->setVariable("APPLET_FILE", $this->object->getFlashPathWeb() . $this->object->getApplet());
		$questionoutput = $template->get();
		
		$pageoutput = $this->outQuestionPage("", $is_postponed, $active_id, $questionoutput);
		return $pageoutput;
	}

	/**
	* Saves the feedback for a single choice question
	*
	* @access public
	*/
	function saveFeedback()
	{
		include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
		$this->object->saveFeedbackGeneric(0, ilUtil::stripSlashes($_POST["feedback_incomplete"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment")));
		$this->object->saveFeedbackGeneric(1, ilUtil::stripSlashes($_POST["feedback_complete"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment")));
		$this->object->cleanupMediaObjectUsage();
		parent::saveFeedback();
	}

	/**
	* Creates the output of the feedback page for a single choice question
	*
	* @access public
	*/
	function feedback()
	{
		$template = new ilTemplate("tpl.il_as_qpl_flash_question_feedback.html",TRUE, TRUE, "Modules/TestQuestionPool");
		$template->setVariable("FEEDBACK_TEXT", $this->lng->txt("feedback"));
		$template->setVariable("FEEDBACK_COMPLETE", $this->lng->txt("feedback_complete_solution"));
		$template->setVariable("VALUE_FEEDBACK_COMPLETE", ilUtil::prepareFormOutput($this->object->prepareTextareaOutput($this->object->getFeedbackGeneric(1)), FALSE));
		$template->setVariable("FEEDBACK_INCOMPLETE", $this->lng->txt("feedback_incomplete_solution"));
		$template->setVariable("VALUE_FEEDBACK_INCOMPLETE", ilUtil::prepareFormOutput($this->object->prepareTextareaOutput($this->object->getFeedbackGeneric(0)), FALSE));
		$template->setVariable("FEEDBACK_ANSWERS", $this->lng->txt("feedback_answers"));
		$template->setVariable("SAVE", $this->lng->txt("save"));
		$template->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		include_once "./Services/RTE/classes/class.ilRTE.php";
		$rtestring = ilRTE::_getRTEClassname();
		include_once "./Services/RTE/classes/class.$rtestring.php";
		$rte = new $rtestring();
		$rte->addPlugin("latex");
		$rte->addButton("latex");
		include_once "./classes/class.ilObject.php";
		$obj_id = $_GET["q_id"];
		$obj_type = ilObject::_lookupType($_GET["ref_id"], TRUE);
		$rte->addRTESupport($obj_id, $obj_type, "assessment");
		$this->tpl->setVariable("ADM_CONTENT", $template->get());
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
			$commands = $_POST["cmd"];
			if (is_array($commands))
			{
				foreach ($commands as $key => $value)
				{
					if (preg_match("/^suggestrange_.*/", $key, $matches))
					{
						$force_active = true;
					}
				}
			}
			// edit question properties
			$ilTabs->addTarget("edit_properties",
				$url,
				array("editQuestion", "save", "cancel", 
					"flashAddParam", "saveEdit"),
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
}
?>
