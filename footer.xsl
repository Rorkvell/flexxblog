<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet version="1.0" 
	xmlns:xsl	= "http://www.w3.org/1999/XSL/Transform"
	xmlns:xlink	= "http://www.w3.org/1999/xlink"
	xmlns:dc	= "http://purl.org/dc/elements/1.1/"
	xmlns:dct="http://purl.org/dc/terms/"
 	xmlns		= "http://www.w3.org/1999/xhtml" 
	exclude-result-prefixes="xsl">


<xsl:template name="FOOTER">
	<xsl:element name="div">
		<xsl:attribute name="id">
			<xsl:text>contentinfo</xsl:text>
		</xsl:attribute>
		<xsl:element name="script">
			<xsl:attribute name="type">
				<xsl:text>text/javascript</xsl:text>
			</xsl:attribute>
			<xsl:attribute name="src">
				<xsl:text>http://www.rorkvell.de/browserupdate.js</xsl:text>
			</xsl:attribute>
			<xsl:comment>browser-update.org</xsl:comment>
		</xsl:element>
		<xsl:element name="ul">
			<xsl:apply-templates select="/rss/channel/copyright" mode="FOOTER"/>
		</xsl:element>
		
		
	</xsl:element>
</xsl:template>
	
<xsl:template match="copyright" mode="FOOTER">
	<xsl:element name="li">
		<xsl:element name="a">
			<xsl:attribute name="rel"><xsl:text>license</xsl:text></xsl:attribute>
			<xsl:attribute name="href">
				<xsl:value-of select="."/>
			</xsl:attribute>
			<xsl:element name="img">
				<xsl:attribute name="alt">
					<xsl:text>Creative Commons Lizenzvertrag</xsl:text>
				</xsl:attribute>
				<xsl:attribute name="src">
					<xsl:text>http://i.creativecommons.org/l/by/3.0/80x15.png</xsl:text>
				</xsl:attribute>
			</xsl:element>
		</xsl:element>
	</xsl:element>
</xsl:template>

<xsl:template match="outline" mode="BADGE">
	<xsl:element name="li">
		<xsl:choose>
			<xsl:when test="@img and @htmlUrl">
<!--				<xsl:if test="@htmlUrl='http://www.browser-statistik.de/'">
					<xsl:comment>browser-statistik.de - Welche Browser werden benutzt?</xsl:comment>
				</xsl:if> -->
				<xsl:apply-templates select="@before" mode="BADGE" />
				<xsl:element name="a">
					<xsl:apply-templates select="@htmlUrl" mode="BADGE" />
					<xsl:apply-templates select="@rel" mode="BADGE" />
					<xsl:apply-templates select="@img" mode="BADGE" />
				</xsl:element>
<!--				<xsl:if test="@htmlUrl='http://www.browser-statistik.de/'">
					<xsl:comment>browser-statistik.de - Ende</xsl:comment>
				</xsl:if> -->
				<xsl:apply-templates select="@after" mode="BADGE"/>
			</xsl:when>
		</xsl:choose>
	</xsl:element>
</xsl:template>

<xsl:template match="@before" mode="BADGE">
	<xsl:comment><xsl:value-of select="."/></xsl:comment>
</xsl:template>

<xsl:template match="@after" mode="BADGE">
	<xsl:comment><xsl:value-of select="."/></xsl:comment>
</xsl:template>

<xsl:template match="@htmlUrl" mode="BADGE">
	<xsl:attribute name="href">
		<xsl:value-of select="."/>
	</xsl:attribute>
</xsl:template>

<xsl:template match="@rel" mode="BADGE">
	<xsl:attribute name="rel">
		<xsl:value-of select="."/>
	</xsl:attribute>
</xsl:template>

<xsl:template match="@img" mode="BADGE">
	<xsl:element name="img">
		<xsl:attribute name="class">
			<xsl:text>badge</xsl:text>
		</xsl:attribute>
		<xsl:attribute name="src">
			<xsl:value-of select="."/>
		</xsl:attribute>
		<xsl:if test="../@title">
			<xsl:attribute name="alt">
				<xsl:value-of select="../@title"/>
			</xsl:attribute>
		</xsl:if>
	</xsl:element>
</xsl:template>

</xsl:stylesheet>