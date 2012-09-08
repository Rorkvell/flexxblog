<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
	xmlns:xlink	= "http://www.w3.org/1999/xlink"
	xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" 
	xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#" 
	xmlns="http://www.w3.org/1999/xhtml" 
	xmlns:foaf="http://xmlns.com/foaf/0.1/" 
	xmlns:dc="http://purl.org/dc/elements/1.0/" 
	xmlns:doap="http://usefulinc.com/ns/doap#" 
	xmlns:admin="http://webns.net/mvcb/"
	xmlns:fn="http://www.w3.org/2005/xpath-functions">

<xsl:output method="xml" version="1.0" encoding="utf-8" omit-xml-declaration="no" standalone="no" indent="yes"/>
<xsl:strip-space elements="*"/>

<xsl:param name="REF"/>	<!--  URL to search for, put into dc:source -->
<xsl:param name="SRC"/>

<xsl:template match="/">
	<xsl:apply-templates select="html"/>
</xsl:template>

<xsl:template match="html">
	<xsl:element name="rdf:RDF">
		<xsl:element name="rdf:Description">
			<xsl:attribute name="rdf:about">
				<xsl:value-of select="$SRC"/>
			</xsl:attribute>
			<xsl:apply-templates select="/html/@lang"/>
			<xsl:apply-templates select="/html/head/title"/>
			<xsl:apply-templates select="//link[@rel]"/>
			<xsl:apply-templates select="//meta[@name]"/>
			<!--<xsl:comment>******* BODY *******</xsl:comment>-->
			<xsl:choose>
				<xsl:when test="/html/head/meta[@name='robots']">
					<xsl:if test="not(contains(/html/head/meta[@name='robots']/@content, 'noindex'))">
						<xsl:apply-templates select="//a[@rel]"/>
						<xsl:apply-templates select="/html/body" mode="BODY"/>
					</xsl:if>
				</xsl:when>
				<xsl:otherwise>
					<xsl:apply-templates select="//a[@rel]"/>
					<xsl:apply-templates select="/html/body" mode="BODY"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:element>
	</xsl:element>
</xsl:template>

<xsl:template match="@lang">
	<xsl:element name="dc:language">
		<xsl:value-of select="."/>
	</xsl:element>
</xsl:template>

<xsl:template match="title">
	<xsl:element name="dc:title">
		<xsl:value-of select="."/>
	</xsl:element>
</xsl:template>

<!-- ******* LINK ******* -->

<xsl:template match="link[@rel='copyright']|link[@rel='dc:rights']|link[@rel='DC.rights']|link[@rel='license']">
	<xsl:element name="dc:rights">
		<xsl:attribute name="rdf:resource">
			<xsl:value-of select="./@href"/>
		</xsl:attribute>
		<xsl:choose>
			<xsl:when test="./@title">
				<xsl:value-of select="./@title"/>
			</xsl:when>
			<xsl:when test="../meta[@name='copyright']">
				<xsl:value-of select="../meta[@name='copyright']/@content"/>
			</xsl:when>
			<xsl:when test="../meta[@name='dc:rights']">
				<xsl:value-of select="../meta[@name='dc:rights']/@content"/>
			</xsl:when>
			<xsl:when test="../meta[@name='DC.rights']">
				<xsl:value-of select="../meta[@name='DC.rights']/@content"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:text>license</xsl:text>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:element>
</xsl:template>

<xsl:template match="link[@rel='dc:creator']|link[@rel='author']|link[@rev='made']|link[@rel='me']">
	<xsl:element name="dc:creator">
		<xsl:attribute name="rdf:resource">
			<xsl:value-of select="./@href"/>
		</xsl:attribute>
		<xsl:choose>
			<xsl:when test="../meta[@name='author']">
				<xsl:value-of select="../meta[@name='author']/@content"/>
			</xsl:when>
			<xsl:when test="../meta[@name='dc:creator']">
				<xsl:value-of select="../meta[@name='dc:creator']/@content"/>
			</xsl:when>
			<xsl:when test="../meta[@name='DC.creator']">
				<xsl:value-of select="../meta[@name='DC.creator']/@content"/>
			</xsl:when>
			<xsl:when test="./@title">
				<xsl:value-of select="./@title"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:text>author</xsl:text>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:element>
</xsl:template>

<xsl:template match="link[@rel='alternate']">
	<xsl:element name="dc:relation">
<!--		<xsl:attribute name="xlink:type">
			<xsl:text>simple</xsl:text>
		</xsl:attribute>
		<xsl:attribute name="xlink:href">
			<xsl:value-of select="./@href"/>
		</xsl:attribute> -->
		<xsl:attribute name="rdf:resource">
			<xsl:value-of select="./@href"/>
		</xsl:attribute>
		<xsl:if test="./@type">
			<xsl:attribute name="dc:format">
				<xsl:value-of select="./@type"/>
			</xsl:attribute>
		</xsl:if>
		<xsl:choose>
			<xsl:when test="./@title">
				<xsl:value-of select="./@title"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="./@href"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:element>
</xsl:template>


<xsl:template match="link[@rel='stylesheet']|link[@rel='search']|link[@rel='apple-touch-icon-precomposed']|link[@rel='shortcut icon']"/>

<xsl:template match="link">
<!--	<xsl:comment>
		<xsl:text>link: </xsl:text>
		<xsl:value-of select="./@rel"/>
	</xsl:comment>-->
</xsl:template>

<!-- ******* META ******* -->

<xsl:template match="meta[@name='author']|meta[@name='dc:creator']|meta[@name='DC.creator']">
	<xsl:if test="not(../link/@rel='author') and not(../link/@rel='dc:creator') and not(../link/@rel='DC.creator') and not(../link/@rev='made')">
		<xsl:element name="dc:creator">
			<xsl:value-of select="./@content"/>
		</xsl:element>
	</xsl:if>
</xsl:template>

<xsl:template match="meta[@name='description']">
	<xsl:element name="dc:description">
		<xsl:value-of select="./@content"/>
	</xsl:element>
</xsl:template>

<xsl:template match="meta[@name='dc:description']|meta[@name='DC.description']">
	<xsl:if test="not(../meta[@name='description'])">
		<xsl:element name="dc:description">
			<xsl:value-of select="./@content"/>
		</xsl:element>
	</xsl:if>
</xsl:template>

<xsl:template match="meta[@name='date']|meta[name='dc:date']|meta[@name='DC.date']">
	<xsl:element name="dc:date">
		<xsl:value-of select="./@content"/>
	</xsl:element>
</xsl:template>

<xsl:template match="meta[@name='publisher']|meta[@name='dc:publisher']|meta[@name='DC.publisher']">
	<xsl:element name="dc:publisher">
		<xsl:value-of select="./@content"/>
	</xsl:element>
</xsl:template>

<xsl:template match="meta[@name='topic']|meta[@name='dc:subject']|meta[@name='DC.subject']">
	<xsl:element name="dc:subject">
		<xsl:value-of select="./@content"/>
	</xsl:element>
</xsl:template>

<xsl:template match="meta">
<!--	<xsl:comment>
		<xsl:value-of select="@name"/>
		<xsl:text> = </xsl:text>
		<xsl:value-of select="@content"/>
	</xsl:comment>-->
</xsl:template>

<!-- ******* BODY ******* -->

<xsl:template match="a[@rel='copyright']|a[@rel='dc:rights']|a[@rel='DC.rights']|a[@rel='license']">
	<xsl:element name="dc:rights">
		<xsl:value-of select="./@href"/>
	</xsl:element>
</xsl:template>

<xsl:template match="a[@rel='author']|a[@rel='dc:creator']|a[@rel='DC.author']|a[@rev='made']">
	<xsl:element name="dc:creator">
		<xsl:attribute name="rdf:resource">
			<xsl:value-of select="./@href"/>
		</xsl:attribute>
		<xsl:value-of select="."/>
	</xsl:element>
</xsl:template>

<xsl:template match="a">
	<xsl:choose>
		<xsl:when test="starts-with(./@href, $REF)">
			<xsl:element name="dc:source">
				<xsl:attribute name="rdf:resource">
					<xsl:value-of select="./@href"/>
				</xsl:attribute>
				<xsl:value-of select="."/>
			</xsl:element>
		</xsl:when>
		<xsl:otherwise>
<!--			<xsl:comment>
				<xsl:value-of select="./@rel"/>
				<xsl:text>: </xsl:text>
				<xsl:value-of select="."/>
				<xsl:text> (</xsl:text>
				<xsl:value-of select="./@href"/>
				<xsl:text>)</xsl:text>
			</xsl:comment>-->
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<!-- ******* BODY2 ******* -->

<!-- Test -->
<xsl:template match="a[@rel]|a[@class]|a[@rev]" mode="BODY">
<!--<xsl:comment>a rel=<xsl:value-of select='@rel'/>, rev=<xsl:value-of select='@rev'/>, class=<xsl:value-of select='@class'/></xsl:comment>-->
	<xsl:apply-templates mode="BODY"/>
</xsl:template>

<xsl:template match="*|text()" mode="BODY">
	<xsl:apply-templates mode="BODY"/>
</xsl:template>

<xsl:template match="*[@class]" mode="BODY">
	<xsl:choose>
		<xsl:when test="contains(./@class, 'vcard')">
			<xsl:call-template name="META1">
				<xsl:with-param name="CLASS" select="./@class"/>
			</xsl:call-template>
		</xsl:when>
		<xsl:otherwise>
			<xsl:apply-templates mode="BODY"/>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<xsl:template name="META1">
	<xsl:param name="CLASS"/>

	<xsl:choose>
		<xsl:when test="contains($CLASS, ' ')">
			<xsl:call-template name="META1">
				<xsl:with-param name="CLASS" select="substring-before($CLASS, ' ')"/>
			</xsl:call-template>
			<xsl:call-template name="META1">
				<xsl:with-param name="CLASS" select="substring-after($CLASS, ' ')"/>
			</xsl:call-template>
		</xsl:when>
		<xsl:otherwise>
<!--<xsl:comment>META1 <xsl:value-of select="name()"/>: <xsl:value-of select="$CLASS"/></xsl:comment>-->
			<xsl:choose>
				<xsl:when test="$CLASS='author' or $CLASS='dc:creator' or $CLASS='DC.creator'">
					<xsl:call-template name="AUTHOR"/>
				</xsl:when>
				<xsl:when test="contains($CLASS, 'author')">
					<xsl:call-template name="CONTRIBUTOR"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:apply-templates mode="BODY"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:otherwise>
	</xsl:choose>

</xsl:template>

<!-- ******* Author ******* -->

<xsl:template name="AUTHOR">
	<xsl:choose>
		<xsl:when test="contains(./@class, 'vcard')">
			<xsl:choose>
				<xsl:when test="contains(./@class, ' fn')">
					<!-- TODO -->
				</xsl:when>
				<xsl:otherwise>
					<xsl:apply-templates mode="AUTHOR_VCARD"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:when>
		<xsl:otherwise>
			<xsl:apply-templates mode="AUTHOR"/>
			// TODO: Replace with other author handling?
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<xsl:template match="a[@class='fn']" mode="AUTHOR">
</xsl:template>


<!-- ******* Author vCard ******* -->

<xsl:template match="text()" mode="AUTHOR_VCARD"/>

<xsl:template match="*" mode="AUTHOR_VCARD">
	<xsl:apply-templates mode="AUTHOR_VCARD"/>
</xsl:template>

<xsl:template match="*[@class]" mode="AUTHOR_VCARD">
<!--<xsl:comment>AUTHOR VCARD; <xsl:value-of select="name(..)"/>/<xsl:value-of select="name()"/>: <xsl:value-of select="./@class"/> <xsl:value-of select="."/></xsl:comment>-->
	<xsl:choose>
		<xsl:when test="contains(./@class, ' ')">
			// TODO: split
		</xsl:when>
		<xsl:otherwise>
			<xsl:apply-templates mode="AUTHOR_VCARD"/>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<!-- already done before -->
<xsl:template match="a[@rel='author']|a[@rel='dc:creator']|a[@rel='DC.creator']|a[@rev='made']" mode="AUTHOR_VCARD"> 
<!--><xsl:comment>Link: rel=<xsl:value-of select="./@rel"/>, class=<xsl:value-of select="./@class"/></xsl:comment>-->
</xsl:template>

<xsl:template match="a[@class='fn']|a[@class='n']" mode="AUTHOR_VCARD">
	<xsl:element name="dc:creator">
		<xsl:attribute name="rdf:resource">
			<xsl:value-of select="./@href"/>
		</xsl:attribute>
		<xsl:value-of select="."/>
	</xsl:element>
</xsl:template>

<xsl:template match="*[@class='fn']|a[@class='n']" mode="AUTHOR_VCARD">
	<xsl:element name="dc:creator">
		<xsl:value-of select="."/>
	</xsl:element>
</xsl:template>

<!-- ******* CONTRIBUTOR ******* -->
<xsl:template name="CONTRIBUTOR">
<!--<xsl:comment>CONTRIBUTOR; <xsl:value-of select="name(..)"/>/<xsl:value-of select="name()"/>: <xsl:value-of select="./@class"/> <xsl:value-of select="."/></xsl:comment>-->
</xsl:template>


</xsl:stylesheet>
