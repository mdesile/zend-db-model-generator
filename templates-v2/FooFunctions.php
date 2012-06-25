<?php
function getColumn($columns,$columnName) {
	$column=null;
	foreach ($columns as $col) {
		if ($col['field'] == $columnName) {
			$column=$col;
			break;
		}
	}
	if ($column == null) {
		die("getColumn: column not found: ".$columnName);
	}
	return $column;
}

function getisRequiredString($column) {

	$isRequired=false;
	if (array_key_exists('required',$column)) {
		$isRequired=$column['required'];
	}
	return $isRequired ? "true" : "false";
}

function inputFilterPrimaryKey($columns,$columnName) {
	$column = getColumn($columns, $columnName);
	$isRequired = getisRequiredString($column);
	if ($column['phptype'] != 'int') {
		die("not supported: ".var_export($column,1));
	}
	echo "            \$inputFilter->add(\$factory->createInput(array(
	'name'       => '$columnName',
	'required'   => $isRequired,
	'filters' => array(
			array('name'    => 'Int'),
	),
	)));
			";
}

			function inputFilterInt($columns,$columnName) {
			$column = getColumn($columns, $columnName);
			$isRequired = getisRequiredString($column);
			echo "            \$inputFilter->add(\$factory->createInput(array(
			'name'       => '$columnName',
			'required'   => $isRequired,
			'filters' => array(
			array('name'    => 'Int'),
			),
			)));
			";
			}

			function inputFilterBoolean($columns,$columnName) {
			$column = getColumn($columns, $columnName);
			$isRequired = getisRequiredString($column);
			echo "            \$inputFilter->add(\$factory->createInput(array(
			'name'       => '$columnName',
			'required'   => $isRequired,
			)));
			";
			}

			function inputFilterString($columns,$columnName,$charset) {
			$column = getColumn($columns, $columnName);
			$isRequired = getisRequiredString($column);
			$strMaxLength=0;
			if (preg_match('/\((\d+)\)/', $column['type'],$matches) == 1) {
			$strMaxLength=$matches[1];
			}
			echo "            \$inputFilter->add(\$factory->createInput(array(
			'name'     => '$columnName',
			'required' => $isRequired,
			'filters'  => array(
			array('name' => 'StripTags'),
			array('name' => 'StringTrim'),
			),";
			if ($strMaxLength > 0) {
			// TODO: charset here is set to utf8 instead of UTF-8. test if it's ok
			echo "
			'validators' => array(
			array(
			'name'    => 'StringLength',
			'options' => array(
			'encoding' => '$charset',
			'min'      => 1,
			'max'      => $strMaxLength,
				),
				),
				),";
			}
				echo "
				)));
				";
			}



