<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet version="1.0" 
	xmlns:xsl	= "http://www.w3.org/1999/XSL/Transform"
	xmlns:dc	= "http://purl.org/dc/elements/1.1/"
	xmlns:dct="http://purl.org/dc/terms/"
 	xmlns		= "http://www.w3.org/1999/xhtml" 
	exclude-result-prefixes="xsl">


<xsl:template name="NAV">
	<xsl:element name="nav">
		<xsl:element name="ul">
			<xsl:attribute name="class"><xsl:text>nav</xsl:text></xsl:attribute>
			<!--  home -->
			<xsl:element name="li">
				<xsl:element name="a">
					<xsl:attribute name="href">
						<xsl:text>/index</xsl:text>
					</xsl:attribute>
					<xsl:attribute name="rel">
						<xsl:text>top</xsl:text>
					</xsl:attribute>
					<xsl:element name="button">
						<xsl:attribute name="type">
							<xsl:text>button</xsl:text>
						</xsl:attribute>
						<xsl:attribute name="onclick">
							<xsl:text>location.href='/index'</xsl:text>
						</xsl:attribute>							
						<xsl:text>Start</xsl:text>
					</xsl:element>
				</xsl:element>
			</xsl:element>
			<!--  info -->
			<xsl:element name="li">
				<xsl:element name="a">
					<xsl:attribute name="href">
						<xsl:text>/info</xsl:text>
					</xsl:attribute>
					<xsl:attribute name="rel">
						<xsl:text>contents</xsl:text>
					</xsl:attribute>
					<xsl:element name="button">
						<xsl:attribute name="type">
							<xsl:text>button</xsl:text>
						</xsl:attribute>
						<xsl:attribute name="onclick">
							<xsl:text>location.href='/info'</xsl:text>
						</xsl:attribute>							
						<xsl:text>Info</xsl:text>
					</xsl:element>
				</xsl:element>
			</xsl:element>
			<!--  impressum -->
			<xsl:element name="li">
				<xsl:element name="a">
					<xsl:attribute name="href">
						<xsl:text>/impressum</xsl:text>
					</xsl:attribute>
					<xsl:attribute name="rel">
						<xsl:text>me DC.creator</xsl:text>
					</xsl:attribute>
					<xsl:element name="button">
						<xsl:attribute name="type">
							<xsl:text>button</xsl:text>
						</xsl:attribute>
						<xsl:attribute name="onclick">
							<xsl:text>location.href='/impressum'</xsl:text>
						</xsl:attribute>							
						<xsl:text>Impressum</xsl:text>
					</xsl:element>
				</xsl:element>
			</xsl:element>
			<!-- Feed -->
			<xsl:if test="/rss/channel/@xml:id">
				<xsl:element name="li">
					<xsl:element name="a">
						<xsl:attribute name="href">
							<xsl:text>../RorkvellNews</xsl:text>
						</xsl:attribute>
						<xsl:attribute name="rel">
							<xsl:text>up</xsl:text>
						</xsl:attribute>
						<xsl:element name="button">
							<xsl:attribute name="type">
								<xsl:text>button</xsl:text>
							</xsl:attribute>
							<xsl:attribute name="onclick">
								<xsl:text>location.href='../RorkvellNews'</xsl:text>
							</xsl:attribute>							
							<xsl:text>Blog</xsl:text>
						</xsl:element>
					</xsl:element>
				</xsl:element>
			</xsl:if>
		</xsl:element>
		<xsl:if test="$TYPE = 'Blog'">
			<xsl:element name="h4">
				<xsl:text>Neue Kommentare</xsl:text>
			</xsl:element>
			<xsl:element name="div">
				<xsl:attribute name="id">
					<xsl:text>newcomments</xsl:text>
				</xsl:attribute>
			</xsl:element>
 			<xsl:element name="script">
				<xsl:attribute name="type">
					<xsl:text>text/javascript</xsl:text>
				</xsl:attribute>
				<xsl:attribute name="src">
					<xsl:value-of select="$FILE"/><xsl:text>.js</xsl:text>
				</xsl:attribute>
			</xsl:element>
		</xsl:if>
	</xsl:element>
</xsl:template>

<xsl:template name="BREADCRUMB">
	<xsl:element name="ul">
		<xsl:attribute name="class">
			<xsl:text>breadcrumb</xsl:text>
		</xsl:attribute>
		<xsl:element name="li">
			<xsl:element name="a">
				<xsl:attribute name="rel">
					<xsl:text>top</xsl:text>
				</xsl:attribute>
				<xsl:attribute name="href">
					<xsl:text>/index</xsl:text>
				</xsl:attribute>
				<xsl:text>Start</xsl:text>
			</xsl:element>
		</xsl:element>
		<xsl:choose>
			<xsl:when test="/rss/channel/@xml:id">
				<xsl:element name="li">
					<xsl:element name="a">
						<xsl:attribute name="rel">
							<xsl:text>up</xsl:text>
						</xsl:attribute>
						<xsl:attribute name="href">
							<xsl:text>../RorkvellNews</xsl:text>
						</xsl:attribute>
						<xsl:text>Blog</xsl:text>
					</xsl:element>
				</xsl:element>
				<xsl:element name="li">
					<xsl:text>Artikel</xsl:text>
				</xsl:element>
			</xsl:when>
			<xsl:otherwise>
				<xsl:element name="li">
					<xsl:text>Blog</xsl:text>
				</xsl:element>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:element>
</xsl:template>

</xsl:stylesheet>