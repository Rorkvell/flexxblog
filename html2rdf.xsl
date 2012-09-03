<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
	xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" 
	xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#" 
	xmlns="http://www.w3.org/1999/xhtml" 
	xmlns:foaf="http://xmlns.com/foaf/0.1/" 
	xmlns:dc="http://purl.org/dc/elements/1.0/" 
	xmlns:doap="http://usefulinc.com/ns/doap#" 
	xmlns:admin="http://webns.net/mvcb/">

<xsl:output method="xml" version="1.0" encoding="utf-8" omit-xml-declaration="no" standalone="no" indent="yes"/>
<xsl:strip-space elements="*"/>

<xsl:param name="REF" select="'http://www.mittelstandswiki.de'"/>

<xsl:template match="/">
	<xsl:apply-templates select="html"/>
</xsl:template>

<xsl:template match="html">
	<xsl:element name="rdf:RDF">
		<xsl:element name="rdf:Description">
			<xsl:attribute name="rdf:about">
				<xsl:value-of select="./head/link[@rel='dc:identifier']/@href"/>
			</xsl:attribute>
			<xsl:apply-templates select="/html/@lang"/>
			<xsl:apply-templates select="/html/head/title"/>
			<xsl:apply-templates select="//link[@rel]"/>
			<xsl:apply-templates select="//meta[@name]"/>
			<xsl:comment>******* BODY *******</xsl:comment>
			<xsl:apply-templates select="//a[@rel]"/>
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

<xsl:template match="link[@rel='copyright']|link[@rel='dc:rights']|link[@rel='DC.rights']">
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

<xsl:template match="link[@rel='dc:creator']|link[@rel='author']|link[@rev='made']">
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

<xsl:template match="link[@rel='stylesheet']|link[@rel='search']|link[@rel='apple-touch-icon-precomposed']|link[@rel='shortcut icon']"/>

<xsl:template match="link">
<!--	<xsl:comment>
		<xsl:text>link: </xsl:text>
		<xsl:value-of select="./@rel"/>
	</xsl:comment>-->
</xsl:template>

<!-- ******* META ******* -->

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

<xsl:template match="a[@rel='copyright']|a[@rel='dc:rights']">
	<xsl:element name="dc:rights">
		<xsl:value-of select="./@href"/>
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



</xsl:stylesheet>
