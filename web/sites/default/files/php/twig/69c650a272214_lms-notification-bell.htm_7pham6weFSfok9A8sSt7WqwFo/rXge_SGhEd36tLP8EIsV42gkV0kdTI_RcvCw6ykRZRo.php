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

/* modules/custom/lms_notification/templates/lms-notification-bell.html.twig */
class __TwigTemplate_43ae7770d7c8aed3671b7ae9e65fdef1 extends Template
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
        // line 11
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->attachLibrary("lms_notification/bell"), "html", null, true);
        yield "
<style>
  .lms-bell-icon-wrapper {
    position: relative;
    display: inline-block;
    cursor: pointer;
  }

  .lms-bell-icon-wrapper .lms-bell-link {
    color: inherit; /* Inherit color from parent */
    text-decoration: none;
    font-size: 1.1rem; /* Adjust as needed */
  }

  .lms-bell-icon-wrapper .lms-bell-counter {
    position: absolute;
    top: -8px;
    right: -12px;
    background-color: #ff4757; /* Danger color */
    color: white;
    border-radius: 50%;
    padding: 0.15rem 0.35rem;
    font-size: 0.65rem;
    min-width: 14px;
    min-height: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #1a1e23; /* Border color */
    line-height: 1; /* Ensure text is centered vertically */
  }
</style>
<div class=\"lms-bell-icon-wrapper\" title=\"";
        // line 43
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Notifications"));
        yield "\">
  <a href=\"";
        // line 44
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["notifications_url"] ?? null), "html", null, true);
        yield "\" class=\"lms-bell-link\">
    <i class=\"far fa-bell\"></i>
  </a>
  <span class=\"lms-bell-counter\" style=\"display: ";
        // line 47
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar((((($context["unread_count"] ?? null) > 0)) ? ("flex") : ("none")));
        yield ";\">
    ";
        // line 48
        yield (((($context["unread_count"] ?? null) > 99)) ? ("99+") : ($this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["unread_count"] ?? null), "html", null, true)));
        yield "
  </span>

  ";
        // line 52
        yield "  <div class=\"lms-bell-dropdown\">
    <div class=\"lms-bell-dropdown__header\">
      <span>";
        // line 54
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Recent Notifications"));
        yield "</span>
      ";
        // line 55
        if ((($context["unread_count"] ?? null) > 0)) {
            // line 56
            yield "        <span style=\"font-size: 0.75rem; color: #ef4444;\">";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["unread_count"] ?? null), "html", null, true);
            yield " ";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("New"));
            yield "</span>
      ";
        }
        // line 58
        yield "    </div>
    
    <div class=\"lms-bell-dropdown__body\">
      ";
        // line 61
        if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["latest_notifications"] ?? null)) > 0)) {
            // line 62
            yield "        ";
            $context["type_icons"] = ["enrollment" => "fa-user-plus", "quiz_result" => "fa-clipboard-check", "certificate" => "fa-certificate", "payment" => "fa-credit-card", "forum_reply" => "fa-comment", "system" => "fa-bell", "announcement" => "fa-bullhorn", "reminder" => "fa-clock"];
            // line 72
            yield "
        ";
            // line 73
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["latest_notifications"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["notif"]) {
                // line 74
                yield "          ";
                $context["item_url"] = ((CoreExtension::getAttribute($this->env, $this->source, $context["notif"], "link", [], "any", false, false, true, 74)) ? (CoreExtension::getAttribute($this->env, $this->source, $context["notif"], "link", [], "any", false, false, true, 74)) : (($context["notifications_url"] ?? null)));
                // line 75
                yield "          <a href=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["item_url"] ?? null), "html", null, true);
                yield "\" class=\"lms-bell-dropdown-item ";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar((((($tmp =  !CoreExtension::getAttribute($this->env, $this->source, $context["notif"], "read", [], "any", false, false, true, 75)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("lms-bell-dropdown-item--unread") : ("")));
                yield "\">
            <div class=\"lms-bell-dropdown-item__icon\">
              <i class=\"fas ";
                // line 77
                yield (((CoreExtension::getAttribute($this->env, $this->source, ($context["type_icons"] ?? null), CoreExtension::getAttribute($this->env, $this->source, $context["notif"], "type", [], "any", false, false, true, 77), [], "array", true, true, true, 77) &&  !(null === (($_v0 = ($context["type_icons"] ?? null)) && is_array($_v0) || $_v0 instanceof ArrayAccess && in_array($_v0::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v0[CoreExtension::getAttribute($this->env, $this->source, $context["notif"], "type", [], "any", false, false, true, 77)] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["type_icons"] ?? null), CoreExtension::getAttribute($this->env, $this->source, $context["notif"], "type", [], "any", false, false, true, 77), [], "array", false, false, true, 77))))) ? ($this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, (($_v1 = ($context["type_icons"] ?? null)) && is_array($_v1) || $_v1 instanceof ArrayAccess && in_array($_v1::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v1[CoreExtension::getAttribute($this->env, $this->source, $context["notif"], "type", [], "any", false, false, true, 77)] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["type_icons"] ?? null), CoreExtension::getAttribute($this->env, $this->source, $context["notif"], "type", [], "any", false, false, true, 77), [], "array", false, false, true, 77)), "html", null, true)) : ("fa-bell"));
                yield "\"></i>
            </div>
            <div class=\"lms-bell-dropdown-item__content\">
              <h4>";
                // line 80
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["notif"], "title", [], "any", false, false, true, 80), "html", null, true);
                yield "</h4>
              ";
                // line 81
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["notif"], "message", [], "any", false, false, true, 81)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 82
                    yield "                <p>";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Twig\Extension\CoreExtension::striptags(CoreExtension::getAttribute($this->env, $this->source, $context["notif"], "message", [], "any", false, false, true, 82)), "html", null, true);
                    yield "</p>
              ";
                }
                // line 84
                yield "              <div class=\"lms-bell-dropdown-item__time\">";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Twig\Extension\CoreExtension']->formatDate(CoreExtension::getAttribute($this->env, $this->source, $context["notif"], "created", [], "any", false, false, true, 84), "M d - H:i"), "html", null, true);
                yield "</div>
            </div>
          </a>
        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['notif'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 88
            yield "      ";
        } else {
            // line 89
            yield "        <div style=\"padding: 2rem; text-align: center; color: #94a3b8;\">
          <i class=\"fas fa-check-circle\" style=\"font-size: 2rem; margin-bottom: 0.5rem; color: #475569;\"></i>
          <div>";
            // line 91
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("You're all caught up!"));
            yield "</div>
        </div>
      ";
        }
        // line 94
        yield "    </div>
    
    <div class=\"lms-bell-dropdown__footer\">
      <a href=\"";
        // line 97
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["notifications_url"] ?? null), "html", null, true);
        yield "\">";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("View all notifications"));
        yield " <i class=\"fas fa-arrow-right\" style=\"font-size: 0.8rem; margin-left: 0.25rem;\"></i></a>
    </div>
  </div>
</div>
";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["notifications_url", "unread_count", "latest_notifications"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "modules/custom/lms_notification/templates/lms-notification-bell.html.twig";
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
        return array (  192 => 97,  187 => 94,  181 => 91,  177 => 89,  174 => 88,  163 => 84,  157 => 82,  155 => 81,  151 => 80,  145 => 77,  137 => 75,  134 => 74,  130 => 73,  127 => 72,  124 => 62,  122 => 61,  117 => 58,  109 => 56,  107 => 55,  103 => 54,  99 => 52,  93 => 48,  89 => 47,  83 => 44,  79 => 43,  44 => 11,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "modules/custom/lms_notification/templates/lms-notification-bell.html.twig", "/var/www/html/web/modules/custom/lms_notification/templates/lms-notification-bell.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["if" => 55, "set" => 62, "for" => 73];
        static $filters = ["escape" => 11, "t" => 43, "length" => 61, "striptags" => 82, "date" => 84];
        static $functions = ["attach_library" => 11];

        try {
            $this->sandbox->checkSecurity(
                ['if', 'set', 'for'],
                ['escape', 't', 'length', 'striptags', 'date'],
                ['attach_library'],
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
