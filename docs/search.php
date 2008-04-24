<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<!--
        This file is autogenerated from the PHP output
        Do not edit this file. Changes will be lost.
      -->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
    <link rel="stylesheet" type="text/css" href="main.css" />
    <link rel="SHORTCUT ICON" href="32favicon.png" />
    <title>Search the documentation on Libvir.org</title>
    <meta name="description" content="libvirt, virtualization, virtualization API" />
  </head>
  <body>
    <div id="header">
      <div id="headerLogo"></div>
    </div>
    <div id="body">
      <div id="content">
<?php
    $query = $HTTP_GET_VARS[ "query" ];
    $scope = $HTTP_GET_VARS[ "scope" ];
    // We handle only the first argument so far
    $query = $_GET['query'];
    $query = ltrim ($query);
    if (! $query) {
        echo "<h1 align='center'>Search the documentation on Libvir.org</h1>";
    }
    if ($scope == NULL)
        $scope = "any";
    $scope = ltrim ($scope);
    if ($scope == "")
        $scope = "any";

?>
<p> The search service indexes the libvirt APIs and documentation as well as the libvir-list@redhat.com mailing-list archives. To use it simply provide a set of keywords:</p>
<form action="<?php echo "$PHP_SELF", "?query=", rawurlencode($query) ?>"
      enctype="application/x-www-form-urlencoded" method="get">
  <input name="query" type="text" size="50" value="<?php echo $query?>"/>
  <select name="scope">
    <option value="any">Search All</option>
    <option value="API" <?php if ($scope == 'API') print "selected='selected'"?>>Only the APIs</option>
    <option value="DOCS" <?php if ($scope == 'DOCS') print "selected"?>>Only the Documentation</option>
    <option value="LISTS" <?php if ($scope == 'LISTS') print "selected"?>>Only the lists archives</option>
  </select>
  <input name="submit" type="submit" value="Search ..."/>
</form>
<?php
    function logQueryWord($word) {
        $result = mysql_query ("SELECT ID,Count FROM Queries WHERE Value='$word'");
	if ($result) {
	    $i = mysql_num_rows($result);
	    if ($i == 0) {
	        mysql_free_result($result);
		mysql_query ("INSERT INTO Queries (Value,Count) VALUES ('$word',1)");
	    } else {
	        $id = mysql_result($result, 0, 0);
		$count = mysql_result($result, 0, 1);
		$count ++;
		mysql_query ("UPDATE Queries SET Count=$count WHERE ID=$id");
	    }
	} else {
	    mysql_query ("INSERT INTO Queries (Value,Count) VALUES ('$word',1)");
	}
    }
    function queryWord($word) {
        $result = NULL;
	$j = 0;
        if ($word) {
	    $result = mysql_query ("SELECT words.relevance, symbols.name, symbols.type, symbols.module, symbols.descr FROM words, symbols WHERE LCASE(words.name) LIKE LCASE('$word') and words.symbol = symbols.name ORDER BY words.relevance DESC LIMIT 75");
	    if ($result) {
		$j = mysql_num_rows($result);
		if ($j == 0)
		    mysql_free_result($result);
	    }
	    logQueryWord($word);
	}
	return array($result, $j);
    }
    function queryHTMLWord($word) {
        $result = NULL;
	$j = 0;
        if ($word) {
	    $result = mysql_query ("SELECT relevance, name, id, resource, section FROM wordsHTML WHERE LCASE(name) LIKE LCASE('$word') ORDER BY relevance DESC LIMIT 75");
	    if ($result) {
		$j = mysql_num_rows($result);
		if ($j == 0)
		    mysql_free_result($result);
	    }
	    logQueryWord($word);
	}
	return array($result, $j);
    }
    function queryArchiveWord($word) {
        $result = NULL;
	$j = 0;
        if ($word) {
	    $result = mysql_query ("SELECT wordsArchive.relevance, wordsArchive.name, 'libvir-list', archives.resource, archives.title FROM wordsArchive, archives WHERE LCASE(wordsArchive.name) LIKE LCASE('$word') and wordsArchive.ID = archives.ID ORDER BY relevance DESC LIMIT 75");
	    if ($result) {
		$j = mysql_num_rows($result);
		if ($j == 0)
		    mysql_free_result($result);
	    }
	    logQueryWord($word);
	}
	return array($result, $j);
    }
    function resSort ($a, $b) {
	list($ra,$ta,$ma,$na,$da) = $a;
	list($rb,$tb,$mb,$nb,$db) = $b;
	if ($ra == $rb) return 0;
	return ($ra > $rb) ? -1 : 1;
    }
    if (($query) && (strlen($query) <= 50)) {
	$link = mysql_connect ("localhost", "nobody");
	if (!$link) {
	    echo "<p> Could not connect to the database: ", mysql_error();
	} else {
	    mysql_select_db("libvir", $link);
	    $list = explode (" ", $query);
	    $results = array();
	    $number = 0;
	    for ($number = 0;$number < count($list);$number++) {

		$word = $list[$number];
		if (($scope == 'any') || ($scope == 'API')) {
		    list($result, $j) = queryWord($word);
		    if ($j > 0) {
			for ($i = 0; $i < $j; $i++) {
			    $relevance = mysql_result($result, $i, 0);
			    $name = mysql_result($result, $i, 1);
			    $type = mysql_result($result, $i, 2);
			    $module = mysql_result($result, $i, 3);
			    $desc = mysql_result($result, $i, 4);
			    if (array_key_exists($name, $results)) {
				list($r,$t,$m,$d,$w,$u) = $results[$name];
				$results[$name] = array(($r + $relevance) * 2,
							$t,$m,$d,$w,$u);
			    } else {
				$id = $name;
				$m = strtolower($module);
				$url = "html/libvirt-$module.html#$id";
				$results[$name] = array($relevance,$type,
						$module, $desc, $name, $url);
			    }
			}
			mysql_free_result($result);
		    }
		}
		if (($scope == 'any') || ($scope == 'DOCS')) {
		    list($result, $k) = queryHTMLWord($word);
		    if ($k > 0) {
			for ($i = 0; $i < $k; $i++) {
			    $relevance = mysql_result($result, $i, 0);
			    $name = mysql_result($result, $i, 1);
			    $id = mysql_result($result, $i, 2);
			    $module = mysql_result($result, $i, 3);
			    $desc = mysql_result($result, $i, 4);
			    $url = $module;
			    if ($id != "") {
				$url = $url + "#$id";
			    }
			    $results["$name _html_ $number _ $i"] =
					  array($relevance, "XML docs",
						$module, $desc, $name, $url);
			}
			mysql_free_result($result);
		    }
		}
		if (($scope == 'any') || ($scope == 'LISTS')) {
		    list($result, $j) = queryArchiveWord($word);
		    if ($j > 0) {
			for ($i = 0; $i < $j; $i++) {
			    $relevance = mysql_result($result, $i, 0);
			    $name = mysql_result($result, $i, 1);
			    $type = mysql_result($result, $i, 2);
			    $url = mysql_result($result, $i, 3);
			    $desc = mysql_result($result, $i, 4);
			    if (array_key_exists($url, $results)) {
				list($r,$t,$m,$d,$w,$u) = $results[$url];
				$results[$name] = array(($r + $relevance) * 2,
							$t,$m,$d,$w,$u);
			    } else {
				$id = $name;
				$m = strtolower($module);
				$u = str_replace(
			"http://www.redhat.com/archives/libvir-list/", "", $url);
				$results[$url] = array($relevance,$type,
						$u, $desc, $name, $url);
			    }
			}
			mysql_free_result($result);
		    }
		}
	    }
	    if ((count($results) == 0) && (count($list) == 1)) {
		$word = $list[0];
		if (($scope == 'any') || ($scope == 'XMLAPI')) {
		    list($result, $j) = queryWord("vir$word");
		    if ($j > 0) {
			for ($i = 0; $i < $j; $i++) {
			    $relevance = mysql_result($result, $i, 0);
			    $name = mysql_result($result, $i, 1);
			    $type = mysql_result($result, $i, 2);
			    $module = mysql_result($result, $i, 3);
			    $desc = mysql_result($result, $i, 4);
			    if (array_key_exists($name, $results)) {
				list($r,$t,$m,$d,$w,$u) = $results[$name];
				$results[$name] = array(($r + $relevance) * 2,
							$t,$m,$d,$w,$u);
			    } else {
				$id = $name;
				$m = strtolower($module);
				$url = "html/libvirt-$module.html#$id";
				$results[$name] = array($relevance,$type,
						$module, $desc, $name, $url);
			    }
			}
			mysql_free_result($result);
		    }
		}
	    }
	    mysql_close($link);
	    $nb = count($results);
	    echo "<h3 align='center'>Found $nb results for query $query</h3>\n";
	    usort($results, "resSort");

            if ($nb > 0) {
		printf("<table><tbody>\n");
		printf("<tr><td>Quality</td><td>Symbol</td><td>Type</td><td>module</td><td>Description</td></tr>\n");
		$i = 0;
		while (list ($name, $val) = each ($results)) {
		    list($r,$t,$m,$d,$s,$u) = $val;
		    $m = str_replace("<", "&lt;", $m);
		    $s = str_replace("<", "&lt;", $s);
		    $d = str_replace("<", "&lt;", $d);
		    echo "<tr><td>$r</td><td><a href='$u'>$s</a></td><td>$t</td><td>$m</td><td>$d</td></tr>";
		    $i = $i + 1;
		    if ($i > 75)
		        break;
		}
		printf("</tbody></table>\n");
	    }
	}
    }
?>
      <img src="libvirtLogo.png" alt="libvirt Logo" />
      </div>
      <div id="menu">
        <ul class="l0"><li>
            <span class="active">Home</span>
          </li><li>
            <a title="Details of new features and bugs fixed in each release" class="inactive" href="news.html">News</a>
          </li><li>
            <a title="Get the latest source releases, binary builds and get access to the source repository" class="inactive" href="downloads.html">Downloads</a>
          </li><li>
            <a title="Information for users, administrators and developers" class="inactive" href="docs.html">Documentation</a>
          </li><li>
            <a title="User contributed content" class="inactive" href="http://wiki.libvirt.org">Wiki</a>
          </li><li>
            <a title="Frequently asked questions" class="inactive" href="FAQ.html">FAQ</a>
          </li><li>
            <a title="How and where to report bugs and request features" class="inactive" href="bugs.html">Bug reports</a>
          </li><li>
            <a title="How to contact the developers via email and IRC" class="inactive" href="contact.html">Contact</a>
          </li><li>
            <a title="Miscellaneous links of interest related to libvirt" class="inactive" href="relatedlinks.html">Related Links</a>
          </li><li>
            <a title="Overview of all content on the website" class="inactive" href="sitemap.html">Sitemap</a>
          </li></ul>
      </div>
    </div>
    <div id="footer">
      <div id="projects">
        <dl id="p1"><dt>
            <a href="http://augeas.net/">Augeas</a>
          </dt><dd>
            <span>A configuration editing tool and API</span>
          </dd><dt>
            <a href="http://libvirt.org/">libvirt</a>
          </dt><dd>
            <span>The open source virtualization API</span>
          </dd></dl>
        <dl id="p2"><dt>
            <a href="http://cobbler.et.redhat.com/">Cobbler</a>
          </dt><dd>
            <span>OS provisioning and profile management</span>
          </dd><dt>
            <a href="http://ovirt.org/">oVirt</a>
          </dt><dd>
            <span>Virtualization management across the data center</span>
          </dd></dl>
        <dl id="p3"><dt>
            <a href="http://freeipa.org/">FreeIPA</a>
          </dt><dd>
            <span>Identity, policy and audit management</span>
          </dd><dt>
            <a href="http://virt-manager.org/">Virtual Machine Manager</a>
          </dt><dd>
            <span>Virtualization management from the desktop</span>
          </dd></dl>
      </div>
    </div>
  </body>
</html>
