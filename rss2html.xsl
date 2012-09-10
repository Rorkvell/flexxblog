<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet version="1.0" 
	xmlns:xsl	= "http://www.w3.org/1999/XSL/Transform"
	xmlns:xlink	= "http://www.w3.org/1999/xlink"
	xmlns:dc	= "http://purl.org/dc/elements/1.1/"
	xmlns:dct="http://purl.org/dc/terms/"
 	xmlns		= "http://www.w3.org/1999/xhtml" 
	exclude-result-prefixes="xsl">

<!-- http://www.w3schools.com/xsl/el_output.asp -->
<xsl:output method="html"
	omit-xml-declaration="yes"
	media-type="text/html"
	indent="yes"
	encoding="utf-8"
	doctype-system="about:legacy-compat"/>

<xsl:param name="FORMAT">
	<xsl:choose>
		<xsl:when test="document('')/xsl:stylesheet/xsl:output/@method = 'html'">
			<xsl:text>html</xsl:text>
		</xsl:when>
		<xsl:otherwise>
			<xsl:text>xml</xsl:text>
		</xsl:otherwise>
	</xsl:choose>
</xsl:param>

<xsl:param name="FILE">
	<xsl:value-of select="substring-before(/rss/channel/link, '.html')"/>
</xsl:param>

<xsl:param name="LANG">
	<xsl:text>.</xsl:text>
	<xsl:value-of select="/rss/channel/language"/>
</xsl:param>


<xsl:include href="style.xsl"/>
<xsl:include href="nav.xsl"/>
<xsl:include href="../footer.xsl"/>
	

<xsl:template match="/">
	<xsl:apply-templates/>
</xsl:template>

<xsl:template match="/rss">
	<xsl:element name="html">
		<xsl:apply-templates select="channel/language"/>
		<xsl:apply-templates select="channel"/>
	</xsl:element>
</xsl:template>

<xsl:template match="language">
	<xsl:choose>
		<xsl:when test="document('')/xsl:stylesheet/xsl:output/@method = 'html'">
			<xsl:attribute name="lang">
				<xsl:value-of select="."/>
			</xsl:attribute>
		</xsl:when>
		<xsl:otherwise>
			<xsl:attribute name="xml:lang">
				<xsl:value-of select="."/>
			</xsl:attribute>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<xsl:template match="channel">
	<xsl:element name="head">
		<xsl:apply-templates mode="HEAD"/>
		<!-- Viewport meta for smartphones -->
		<xsl:element name="meta">
			<xsl:attribute name="name">
				<xsl:text>viewport</xsl:text>
			</xsl:attribute>
			<xsl:attribute name="content">
				<xsl:text>user-scalable=yes, width=device-width, initial-scale=1.0</xsl:text>
			</xsl:attribute>
		</xsl:element>
		<xsl:choose>
			<xsl:when test="document('')/xsl:stylesheet/xsl:output/@method = 'html'">
				<!-- Add styles for html -->
				<xsl:call-template name="HTMLSTYLE"/>
			</xsl:when>
			<xsl:otherwise>
				<!-- Add styles for xml resp. xhtml -->
				<xsl:call-template name="XMLSTYLE"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:element>
	<xsl:element name="body">
		<xsl:element name="header">
			<xsl:apply-templates mode="BANNER"/>
			<xsl:element name="a">
				<xsl:attribute name="id">
					<xsl:text>skiplink</xsl:text>
				</xsl:attribute>
				<xsl:attribute name="href">
					<xsl:text>#main</xsl:text>
				</xsl:attribute>
				<xsl:text>skip to content</xsl:text>
			</xsl:element>
			<xsl:call-template name="BREADCRUMB"/>
		</xsl:element>
		<xsl:call-template name="NAV"/>
		<xsl:element name="div">
			<xsl:attribute name="id">
				<xsl:text>main</xsl:text>
			</xsl:attribute>
			<xsl:apply-templates select="image" mode="BODY"/>
			<xsl:apply-templates select="description" mode="BODY"/>
			<xsl:apply-templates select="pubDate" mode="BODY"/>
			<xsl:apply-templates select="dc:creator" mode="BODY"/>
			<xsl:element name="hr">
				<xsl:attribute name="class"><xsl:text>separator</xsl:text></xsl:attribute>
			</xsl:element>

			<xsl:if test="./item">
				<xsl:if test="not(/rss/channel/@xml:id)">
					<xsl:element name="ol">
						<xsl:apply-templates select="item" mode="TOC"/>
					</xsl:element>
				</xsl:if>
				<xsl:element name="ol">
					<xsl:apply-templates select="item" mode="BODY"/>
				</xsl:element>
			</xsl:if>

			<xsl:if test="/rss/channel/@xml:id">
				<xsl:call-template name="COMMENTFORM"/>
			</xsl:if>
			
		</xsl:element> <!-- div#main -->
		
		<xsl:call-template name="FOOTER"/>
		
	</xsl:element>
</xsl:template>


<!-- HEAD -->

<xsl:template match="title" mode="HEAD">
	<xsl:element name="title">
		<xsl:value-of select="normalize-space(.)"/>
	</xsl:element>
</xsl:template>

<xsl:template match="description" mode="HEAD">
</xsl:template>

<xsl:template match="copyright" mode="HEAD">
	<xsl:choose>
		<xsl:when test="starts-with(., 'http://')">
			<xsl:element name="link">
				<xsl:attribute name="rel"><xsl:text>DC.rights</xsl:text></xsl:attribute>
				<xsl:attribute name="href"><xsl:value-of select="."/></xsl:attribute>
			</xsl:element>
		</xsl:when>
		<xsl:otherwise>
			<xsl:element name="meta">
				<xsl:attribute name="name"><xsl:text>DC.rights</xsl:text></xsl:attribute>
				<xsl:attribute name="content"><xsl:value-of select="."/></xsl:attribute>
			</xsl:element>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<xsl:template match="managingEditor" mode="HEAD">	<!-- moved to dc:creator element -->
	<xsl:if test="not(../dc:creator)">
		<xsl:if test="./@xlink:href">
			<xsl:element name="link">
				<xsl:attribute name="rel">
					<xsl:text>author</xsl:text>
				</xsl:attribute>
				<xsl:attribute name="href">
					<xsl:value-of select="./@xlink:href"/>
				</xsl:attribute>
			</xsl:element>
		</xsl:if>
		<xsl:element name="meta">
			<xsl:attribute name="name">
				<xsl:text>author</xsl:text>
			</xsl:attribute>
			<xsl:attribute name="content">
				<xsl:value-of select="."/>
			</xsl:attribute>
		</xsl:element>
	</xsl:if>
</xsl:template>

<xsl:template match="item" mode="HEAD">
	<xsl:apply-templates select="guid" mode="HEAD"/>
</xsl:template>

<xsl:template match="link" mode="HEAD">
	<xsl:element name="link">
		<xsl:attribute name="rel">
			<xsl:text>dc:identifier</xsl:text>
		</xsl:attribute>
		<xsl:attribute name="href">
			<xsl:value-of select="."/>
		</xsl:attribute>
	</xsl:element>
</xsl:template>

<!--  DCTERMS see http://dublincore.org/documents/dc-html/ -->
<xsl:template match="pubDate" mode="HEAD">
	<xsl:element name="meta">
		<xsl:attribute name="name">
			<xsl:text>DCTERMS.date.created</xsl:text>
		</xsl:attribute>
		<xsl:attribute name="content">
			<xsl:value-of select="."/>
		</xsl:attribute>
	</xsl:element>
</xsl:template>

<xsl:template match="lastBuildDate" mode="HEAD">
	<xsl:element name="meta">
		<xsl:attribute name="name">
			<xsl:text>DCTERMS.date.modified</xsl:text>
		</xsl:attribute>
		<xsl:attribute name="content">
			<xsl:value-of select="."/>
		</xsl:attribute>
	</xsl:element>
</xsl:template>

<xsl:template match="guid" mode="HEAD">
	<xsl:element name="link">
		<xsl:attribute name="rel"><xsl:text>bookmark</xsl:text></xsl:attribute>
		<xsl:attribute name="href">
			<xsl:value-of select="."/>
		</xsl:attribute>
	</xsl:element>
</xsl:template>

<xsl:template match="dc:subject" mode="HEAD">
	<xsl:element name="meta">
		<xsl:attribute name="name"><xsl:text>keywords</xsl:text></xsl:attribute>
		<xsl:attribute name="content">
			<xsl:value-of select="."/>
		</xsl:attribute>
	</xsl:element>
</xsl:template>

<xsl:template match="dc:identifier" mode="HEAD">
	<xsl:element name="link">
		<xsl:attribute name="rel">
			<xsl:text>alternate</xsl:text>
		</xsl:attribute>
		<xsl:attribute name="type">
			<xsl:text>application/xml</xsl:text>
		</xsl:attribute>
		<xsl:attribute name="href">
			<xsl:value-of select="."/>
		</xsl:attribute>
	</xsl:element>
</xsl:template>

<xsl:template match="dc:creator" mode="HEAD">
	<xsl:if test="./@xlink:href">
		<xsl:element name="link">
			<xsl:attribute name="rel">
				<xsl:text>author</xsl:text>
			</xsl:attribute>
			<xsl:attribute name="href">
				<xsl:value-of select="./@xlink:href"/>
			</xsl:attribute>
		</xsl:element>
	</xsl:if>
	<xsl:element name="meta">
		<xsl:attribute name="name">
			<xsl:text>author</xsl:text>
		</xsl:attribute>
		<xsl:attribute name="content">
			<xsl:value-of select="."/>
		</xsl:attribute>
	</xsl:element>
</xsl:template>


<xsl:template match="image|webMaster|docs" mode="HEAD"/>

<xsl:template match="*|@*|text()" mode="HEAD"/>

<!-- BODY -->

<!-- Banner -->

<xsl:template match="title" mode="BANNER">
	<xsl:element name="h1">
		<xsl:value-of select="normalize-space(.)"/>
	</xsl:element>
	<xsl:element name="a">
		<xsl:attribute name="rel"><xsl:text>skiplink</xsl:text></xsl:attribute>
		<xsl:attribute name="href">
			<xsl:text>#main</xsl:text>
		</xsl:attribute>
	</xsl:element>
</xsl:template>

<!--  
<xsl:template match="description" mode="BANNER">
	<xsl:element name="p">
		<xsl:value-of select="normalize-space(.)"/>
	</xsl:element>
</xsl:template>
 -->
<xsl:template match="*|@*|text()" mode="BANNER"/>

<!-- Top -->

<xsl:template match="image" mode="BODY">
	<xsl:apply-templates select="url" mode="LOGO"/>
</xsl:template>

<xsl:template match="url" mode="LOGO">
	<xsl:element name="img">
		<xsl:attribute name="src">
			<xsl:value-of select="."/>
		</xsl:attribute>
		<xsl:attribute name="id">
			<xsl:text>main_logo</xsl:text>
		</xsl:attribute>
		<xsl:apply-templates select="../title" mode="LOGO"/>
	</xsl:element>
</xsl:template>

<xsl:template match="title" mode="LOGO">
	<xsl:attribute name="alt">
		<xsl:value-of select="."/>
	</xsl:attribute>
</xsl:template>


<xsl:template match="description" mode="BODY">
	<xsl:element name="article">
		<xsl:attribute name="class">
			<xsl:text>entry-content</xsl:text>
		</xsl:attribute>
		<xsl:value-of select="normalize-space(.)" disable-output-escaping="yes"/>
	</xsl:element>
</xsl:template>

<xsl:template match="@dc:date">
	<xsl:attribute name="datetime">
		<xsl:value-of select="."/>
	</xsl:attribute>
</xsl:template>

<xsl:template match="pubDate" mode="BODY">
	<xsl:element name="p">
		<xsl:element name="time">
			<xsl:apply-templates select="@dc:date"/>
			<xsl:value-of select="."/>
		</xsl:element>
	</xsl:element>
</xsl:template>
			
<xsl:template match="dc:creator" mode="BODY">
	<xsl:element name="p">
		<xsl:attribute name="class">
			<xsl:text>vcard fn</xsl:text>
		</xsl:attribute>
		<xsl:choose>
			<xsl:when test="./@xlink:href">
				<xsl:element name="a">
					<xsl:attribute name="href">
						<xsl:value-of select="./@xlink:href"/>
					</xsl:attribute>
					<xsl:attribute name="rel">
						<xsl:text>dc:creator</xsl:text>
					</xsl:attribute>
					<xsl:value-of select="."/>
				</xsl:element>
			</xsl:when>
			<xsl:otherwise>
				<xsl:element name="span">
					<xsl:attribute name="class">
						<xsl:text>dc:creator</xsl:text>
					</xsl:attribute>
					<xsl:value-of select="."/>
				</xsl:element>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:element>
</xsl:template>

<!-- Items -->

<xsl:template match="item" mode="TOC">
	<xsl:if test="./@xml:id">
		<xsl:if test="./title">
			<xsl:element name="li">
				<xsl:element name="a">
					<xsl:attribute name="href">
						<xsl:text>#</xsl:text><xsl:value-of select="./@xml:id"/>
					</xsl:attribute>
					<xsl:value-of select="./title"/>
				</xsl:element>
			</xsl:element>
		</xsl:if>
	</xsl:if>
</xsl:template>

<xsl:template match="item" mode="BODY">
	<xsl:element name="li">
		<xsl:element name="article">
			<xsl:apply-templates select="@xml:id"/>
			<xsl:apply-templates select="title" mode="ITEM"/>
			<xsl:apply-templates select="pubDate" mode="ITEM"/>
			<xsl:apply-templates select="description" mode="ITEM"/>
			<xsl:apply-templates select="author" mode="ITEM"/>
			<xsl:if test="category">
				<xsl:element name="ul">
					<xsl:attribute name="class">
						<xsl:text>categories</xsl:text>
					</xsl:attribute>
					<xsl:apply-templates select="category" mode="ITEM"/>
				</xsl:element>
			</xsl:if>
			<xsl:apply-templates select="guid" mode="ITEM"/>
		</xsl:element>
	</xsl:element>
</xsl:template>

<xsl:template match="author" mode="ITEM">
	<xsl:element name="p">
		<xsl:choose>
			<xsl:when test="./@xlink:href">
				<xsl:element name="a">
					<xsl:attribute name="href">
						<xsl:value-of select="./@xlink:href"/>
					</xsl:attribute>
					<xsl:value-of select="."/>
				</xsl:element>
			</xsl:when>
			<xsl:when test="../source">
				<xsl:element name="a">
					<xsl:attribute name="href">
						<xsl:value-of select="../source/@url"/>
					</xsl:attribute>
					<xsl:value-of select="."/>
				</xsl:element>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="."/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:element>
</xsl:template>

<xsl:template match="@xml:id">
	<xsl:attribute name="id">
		<xsl:value-of select="."/>
	</xsl:attribute>
</xsl:template>

<xsl:template match="title" mode="ITEM">
	<xsl:element name="h3">
	<!-- 	<xsl:apply-templates select="../@xml:id"/> -->
		<xsl:choose>
			<xsl:when test="../guid/@isPermaLink='true'">
				<xsl:element name="a">
					<xsl:attribute name="href">
						<xsl:value-of select="../guid"/>
					</xsl:attribute>
					<xsl:value-of select="."/>
				</xsl:element>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="."/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:element>
</xsl:template>

<xsl:template match="guid" mode="ITEM">
	<xsl:if test="./@isPermaLink='true'">
		<xsl:element name="p">
			<xsl:element name="a">
				<xsl:attribute name="href">
					<xsl:value-of select="."/>
				</xsl:attribute>
				<xsl:text>Kommentare</xsl:text>
			</xsl:element>
		</xsl:element>
	</xsl:if>
</xsl:template>

<xsl:template match="description" mode="ITEM">
	<xsl:element name="div">
		<xsl:attribute name="class">
			<xsl:text>entry-content</xsl:text>
		</xsl:attribute>
		<xsl:value-of select="normalize-space(.)" disable-output-escaping="yes"/>
	</xsl:element>
</xsl:template>

<xsl:template match="pubDate" mode="ITEM">
	<xsl:element name="p">
		<xsl:element name="time">
			<xsl:apply-templates select="@dc:date"/>
			<xsl:attribute name="pubdate"><xsl:text>pubdate</xsl:text></xsl:attribute>
			<xsl:value-of select="."/>
		</xsl:element>
	</xsl:element>
</xsl:template>

<xsl:template match="category" mode="ITEM">
	<xsl:element name="li">
		<xsl:choose>
			<xsl:when test="@domain">
				<xsl:element name="a">
					<xsl:attribute name="href">
						<xsl:value-of select="@domain"/>
					</xsl:attribute>
					<xsl:value-of select="."/>
				</xsl:element>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="."/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:element>
</xsl:template>

<xsl:template match="*|@*|text()"/>

<!--
<xsl:template match="node()|@*">
	<xsl:copy>
		<xsl:apply-templates/>
	</xsl:copy>
</xsl:template>
-->

<xsl:template name="COMMENTFORM">
	<xsl:element name="form">
		<xsl:attribute name="action">
			<xsl:text>../comment.php</xsl:text>
		</xsl:attribute>
		<xsl:attribute name="method">
			<xsl:text>post</xsl:text>
		</xsl:attribute>
		<xsl:element name="fieldset">
			<xsl:attribute name="id">
				<xsl:text>comment</xsl:text>
			</xsl:attribute>
			<xsl:element name="legend">
				<xsl:text>Kommentar</xsl:text>
			</xsl:element>
			<xsl:element name="p">
				<xsl:text>Zum Kommentieren sind zwei einfache Regeln zu befolgen:</xsl:text>
			</xsl:element>
			<xsl:element name="ol">
				<xsl:element name="li">
					<xsl:text>Keine persönlichen Beleidigungen</xsl:text>
				</xsl:element>
				<xsl:element name="li">
					<xsl:text>Behauptungen über Personen sind zu belegen</xsl:text>
				</xsl:element>
			</xsl:element>			
			<xsl:element name="ul">
				<xsl:element name="li">
					<xsl:element name="label">
						<xsl:attribute name="for">
							<xsl:text>name</xsl:text>
						</xsl:attribute>
						<xsl:text>Name: </xsl:text>
					</xsl:element>
					<xsl:element name="input">
						<xsl:attribute name="type">
							<xsl:text>text</xsl:text>
						</xsl:attribute>
						<xsl:attribute name="required">
							<xsl:text>required</xsl:text>
						</xsl:attribute>
						<xsl:attribute name="name">
							<xsl:text>name</xsl:text>
						</xsl:attribute>
					</xsl:element>
				</xsl:element> <!--  li -->
				<xsl:element name="li">
					<xsl:element name="label">
						<xsl:attribute name="for">
							<xsl:text>source</xsl:text>
						</xsl:attribute>
						<xsl:text>Webseite: </xsl:text>
					</xsl:element>
					<xsl:element name="input">
						<xsl:attribute name="type">
							<xsl:text>url</xsl:text>
						</xsl:attribute>
						<xsl:attribute name="name">
							<xsl:text>source</xsl:text>
						</xsl:attribute>
					</xsl:element>
				</xsl:element> <!--  li -->
				<xsl:element name="li">
<!--					<xsl:element name="label">
						<xsl:attribute name="for">
							<xsl:text>text</xsl:text>
						</xsl:attribute>
						<xsl:text>Text: </xsl:text>
					</xsl:element> -->
					<xsl:element name="textarea">
						<xsl:attribute name="cols">
							<xsl:text>60</xsl:text>
						</xsl:attribute>
						<xsl:attribute name="rows">
							<xsl:text>12</xsl:text>
						</xsl:attribute>
						<xsl:attribute name="name">
							<xsl:text>text</xsl:text>
						</xsl:attribute>
						<xsl:attribute name="required">
							<xsl:text>required</xsl:text>
						</xsl:attribute>
						<xsl:attribute name="name">
							<xsl:text>text</xsl:text>
						</xsl:attribute>
						<xsl:attribute name="placeholder">
							<xsl:text>Kommentar hier eingeben</xsl:text>
						</xsl:attribute>
					</xsl:element>
				</xsl:element> <!--  li -->
				
			</xsl:element>	<!-- ul -->
				
			<xsl:element name="input">
				<xsl:attribute name="type">
					<xsl:text>submit</xsl:text>
				</xsl:attribute>
				<xsl:attribute name="value">
					<xsl:text>o.k.</xsl:text>
				</xsl:attribute>
			</xsl:element>
			
			<!-- hidden -->
			<xsl:element name="input">
				<xsl:attribute name="type">
					<xsl:text>hidden</xsl:text>
				</xsl:attribute>
				<xsl:attribute name="name">
					<xsl:text>id</xsl:text>
				</xsl:attribute>
				<xsl:attribute name="value">
					<xsl:value-of select="/rss/channel/@xml:id"/>
				</xsl:attribute>
			</xsl:element>
			<xsl:element name="input">
				<xsl:attribute name="type">
					<xsl:text>hidden</xsl:text>
				</xsl:attribute>
				<xsl:attribute name="name">
					<xsl:text>link</xsl:text>
				</xsl:attribute>
				<xsl:attribute name="value">
					<xsl:value-of select="/rss/channel/link"/>
				</xsl:attribute>
			</xsl:element>
			<xsl:element name="input">
				<xsl:attribute name="type">
					<xsl:text>hidden</xsl:text>
				</xsl:attribute>
				<xsl:attribute name="name">
					<xsl:text>rss</xsl:text>
				</xsl:attribute>
				<xsl:attribute name="value">
					<xsl:value-of select="/rss/channel/dc:identifier"/>
				</xsl:attribute>
			</xsl:element>
			
				
		</xsl:element>	<!-- fieldset -->
	</xsl:element>
</xsl:template>

</xsl:stylesheet>
