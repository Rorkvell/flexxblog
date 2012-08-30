<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet version="1.0" 
	xmlns:xsl	= "http://www.w3.org/1999/XSL/Transform"
	xmlns:dc	= "http://purl.org/dc/elements/1.1/"
 	xmlns		= "http://www.w3.org/1999/xhtml" 
	exclude-result-prefixes="xsl">

<xsl:template name="XMLSTYLE">
</xsl:template>

<xsl:template name="HTMLSTYLE">
	<xsl:element name="link">
		<xsl:attribute name="rel"><xsl:text>stylesheet</xsl:text></xsl:attribute>
		<xsl:attribute name="href">
			<xsl:text>/style/Basic.css</xsl:text>
		</xsl:attribute>
	</xsl:element>
	<xsl:element name="link">
		<xsl:attribute name="rel"><xsl:text>stylesheet</xsl:text></xsl:attribute>
		<xsl:attribute name="href">
			<xsl:text>/style/Erde/Erde.css</xsl:text>
		</xsl:attribute>
	</xsl:element>
</xsl:template>

</xsl:stylesheet>