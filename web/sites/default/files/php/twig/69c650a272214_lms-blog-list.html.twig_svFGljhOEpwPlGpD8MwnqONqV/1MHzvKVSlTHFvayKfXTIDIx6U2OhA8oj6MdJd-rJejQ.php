<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;
use Twig\TemplateWrapper;

/* modules/custom/lms_blog/templates/lms-blog-list.html.twig */
class __TwigTemplate_82ee8df09c619acac6906ecd2bdba459 extends Template
{
    private Source $source;
    /**
     * @var array<string, Template>
     */
    private array $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->extensions[SandboxExtension::class];
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 7
        yield "<div class=\"blog-container container py-5\">
  <div class=\"row mb-5\">
    <div class=\"col-12 text-center\">
      <h1 class=\"display-4 fw-bold\">Latest News & Insights</h1>
      <p class=\"lead text-muted\">Stay up to date with EverLMS tips, updates, and educational resources.</p>
    </div>
  </div>

  <div class=\"row g-4\">
    ";
        // line 16
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["articles"] ?? null));
        $context['_iterated'] = false;
        foreach ($context['_seq'] as $context["_key"] => $context["article"]) {
            // line 17
            yield "      <div class=\"col-md-4\">
        <div class=\"card h-100 rounded-4 overflow-hidden\" 
             style=\"background-color: #161b22; border: 1px solid rgba(240, 246, 252, 0.1); 
                    transition: all 0.3s ease; box-shadow: 0 4px 6px rgba(0,0,0,0.1);\"
             onmouseover=\"this.style.transform='translateY(-6px)'; this.style.boxShadow='0 12px 24px rgba(0,0,0,0.3)';\"
             onmouseout=\"this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px rgba(0,0,0,0.1)';\">
          ";
            // line 23
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["article"], "thumbnail", [], "any", false, false, true, 23), "entity", [], "any", false, false, true, 23)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 24
                yield "            <img src=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->getFileUrl(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["article"], "thumbnail", [], "any", false, false, true, 24), "entity", [], "any", false, false, true, 24), "uri", [], "any", false, false, true, 24), "value", [], "any", false, false, true, 24)), "html", null, true);
                yield "\" class=\"card-img-top\" alt=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["article"], "title", [], "any", false, false, true, 24), "value", [], "any", false, false, true, 24), "html", null, true);
                yield "\" style=\"height: 200px; object-fit: cover; border-bottom: 1px solid rgba(240, 246, 252, 0.05);\">
          ";
            } else {
                // line 26
                yield "            <div class=\"card-img-top d-flex align-items-center justify-content-center\" style=\"background-color: #21262d; height: 200px;\">
              <span class=\"text-muted\"><i class=\"fas fa-image fa-3x\"></i></span>
            </div>
          ";
            }
            // line 30
            yield "          
          <div class=\"card-body p-4 d-flex flex-column\">
            <h5 class=\"card-title fw-bold mb-3\">
              <a href=\"";
            // line 33
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->getPath("lms_blog.frontend.canonical", ["lms_article" => CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["article"], "id", [], "any", false, false, true, 33), "value", [], "any", false, false, true, 33)]), "html", null, true);
            yield "\" class=\"text-decoration-none stretched-link\" style=\"color: #e6edf3; transition: color 0.2s;\" onmouseover=\"this.style.color='#58a6ff'\" onmouseout=\"this.style.color='#e6edf3'\">
                ";
            // line 34
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["article"], "title", [], "any", false, false, true, 34), "value", [], "any", false, false, true, 34), "html", null, true);
            yield "
              </a>
            </h5>
            <p class=\"card-text text-muted mb-4\" style=\"display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;\">
              ";
            // line 38
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["article"], "seo_description", [], "any", false, true, true, 38), "value", [], "any", true, true, true, 38)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["article"], "seo_description", [], "any", false, false, true, 38), "value", [], "any", false, false, true, 38), (Twig\Extension\CoreExtension::slice($this->env->getCharset(), Twig\Extension\CoreExtension::striptags(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["article"], "content", [], "any", false, false, true, 38), "value", [], "any", false, false, true, 38)), 0, 150) . "..."))) : ((Twig\Extension\CoreExtension::slice($this->env->getCharset(), Twig\Extension\CoreExtension::striptags(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["article"], "content", [], "any", false, false, true, 38), "value", [], "any", false, false, true, 38)), 0, 150) . "..."))), "html", null, true);
            yield "
            </p>
            <div class=\"mt-auto d-flex justify-content-between align-items-center\">
              <small class=\"text-muted\"><i class=\"far fa-calendar-alt me-2\"></i>";
            // line 41
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Twig\Extension\CoreExtension']->formatDate(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["article"], "created", [], "any", false, false, true, 41), "value", [], "any", false, false, true, 41), "M d, Y"), "html", null, true);
            yield "</small>
              <span class=\"text-primary fw-bold\" style=\"font-size: 0.9rem;\">Read more <i class=\"fas fa-arrow-right ms-1\"></i></span>
            </div>
          </div>
        </div>
      </div>
    ";
            $context['_iterated'] = true;
        }
        // line 47
        if (!$context['_iterated']) {
            // line 48
            yield "      <div class=\"col-12 text-center py-5\">
        <div class=\"p-5 rounded-4 shadow-sm\" style=\"background-color: #161b22; border: 1px solid rgba(240, 246, 252, 0.1);\">
          <i class=\"far fa-newspaper fa-4x text-muted mb-3\"></i>
          <h4 style=\"color: #e6edf3;\">No articles published yet</h4>
          <p class=\"text-muted\">Check back later for exciting news from EverLMS.</p>
        </div>
      </div>
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['article'], $context['_parent'], $context['_iterated']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 56
        yield "  </div>
</div>
";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["articles"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "modules/custom/lms_blog/templates/lms-blog-list.html.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable(): bool
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  132 => 56,  119 => 48,  117 => 47,  106 => 41,  100 => 38,  93 => 34,  89 => 33,  84 => 30,  78 => 26,  70 => 24,  68 => 23,  60 => 17,  55 => 16,  44 => 7,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "modules/custom/lms_blog/templates/lms-blog-list.html.twig", "/var/www/html/web/modules/custom/lms_blog/templates/lms-blog-list.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["for" => 16, "if" => 23];
        static $filters = ["escape" => 24, "default" => 38, "slice" => 38, "striptags" => 38, "date" => 41];
        static $functions = ["file_url" => 24, "path" => 33];

        try {
            $this->sandbox->checkSecurity(
                ['for', 'if'],
                ['escape', 'default', 'slice', 'striptags', 'date'],
                ['file_url', 'path'],
                $this->source
            );
        } catch (SecurityError $e) {
            $e->setSourceContext($this->source);

            if ($e instanceof SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

    }
}
