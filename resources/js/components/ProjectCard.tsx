import React from 'react';
import { ExternalLink, Layers, Bot, Smartphone, ArrowRight, type LucideIcon } from 'lucide-react';
import type { PublicProject } from '../services/contentService';

export interface ProjectCardProps {
  project: PublicProject;
  onSelectProject: (title: string, desc: string, tags: string[]) => void;
  index?: number;
  isVisible?: boolean;
  style?: React.CSSProperties;
  className?: string;
}

interface CardStyleScheme {
  gradient: string;
  borderColor: string;
  icon: LucideIcon;
}

export const CARD_STYLE_SCHEMES: CardStyleScheme[] = [
  {
    gradient: 'linear-gradient(135deg, rgba(35, 136, 255, 0.25) 0%, rgba(123, 77, 255, 0.15) 100%)',
    borderColor: 'rgba(35, 136, 255, 0.4)',
    icon: Layers,
  },
  {
    gradient: 'linear-gradient(135deg, rgba(40, 215, 229, 0.25) 0%, rgba(35, 136, 255, 0.15) 100%)',
    borderColor: 'rgba(40, 215, 229, 0.4)',
    icon: Bot,
  },
  {
    gradient: 'linear-gradient(135deg, rgba(123, 77, 255, 0.25) 0%, rgba(40, 215, 229, 0.15) 100%)',
    borderColor: 'rgba(123, 77, 255, 0.4)',
    icon: Smartphone,
  },
];

export const ProjectCard: React.FC<ProjectCardProps> = ({
  project,
  onSelectProject,
  index = 0,
  isVisible = true,
  style,
  className = '',
}) => {
  const scheme = CARD_STYLE_SCHEMES[index % CARD_STYLE_SCHEMES.length];
  const ProjIcon = scheme.icon;
  const technologies = project.technologies || [];
  const description = project.short_description || project.description;

  const handleSelect = () => {
    onSelectProject(project.title, project.description || project.short_description, technologies);
  };

  return (
    <div
      className={`glass-card project-card reveal-item stagger-${(index % 6) + 1} ${
        isVisible ? 'reveal-visible' : ''
      } ${className}`}
      style={{
        display: 'flex',
        flexDirection: 'column',
        background: 'rgba(16, 21, 36, 0.85)',
        position: 'relative',
        overflow: 'hidden',
        borderRadius: '16px',
        ...style,
      }}
    >
      <div
        style={{
          height: '210px',
          background: scheme.gradient,
          borderBottom: '1px solid rgba(41, 50, 71, 0.6)',
          position: 'relative',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          padding: '20px',
        }}
      >
        {project.cover_image && (
          <img
            src={project.cover_image}
            alt={`Capa do projeto ${project.title}`}
            loading="lazy"
            style={{
              position: 'absolute',
              inset: 0,
              width: '100%',
              height: '100%',
              objectFit: 'cover',
            }}
          />
        )}

        {!project.cover_image && (
          <div
            style={{
              width: '85%',
              height: '85%',
              background: 'rgba(8, 11, 20, 0.75)',
              borderRadius: '10px',
              border: `1px solid ${scheme.borderColor}`,
              padding: '16px',
              display: 'flex',
              flexDirection: 'column',
              gap: '10px',
              boxShadow: '0 8px 24px rgba(0,0,0,0.5)',
            }}
          >
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
              <div style={{ display: 'flex', gap: '6px' }}>
                <span style={{ width: '8px', height: '8px', borderRadius: '50%', background: '#FF5F56' }} />
                <span style={{ width: '8px', height: '8px', borderRadius: '50%', background: '#FFBD2E' }} />
                <span style={{ width: '8px', height: '8px', borderRadius: '50%', background: '#27C93F' }} />
              </div>
              <span style={{ fontSize: '10px', color: 'var(--text-gray)' }}>Devs Tomorrow v2.4</span>
            </div>

            <div style={{ display: 'flex', alignItems: 'center', gap: '10px', marginTop: '6px' }}>
              <ProjIcon size={24} color="#28D7E5" />
              <div style={{ flex: 1 }}>
                <div
                  style={{
                    height: '8px',
                    width: '60%',
                    background: 'rgba(40, 215, 229, 0.4)',
                    borderRadius: '4px',
                  }}
                />
                <div
                  style={{
                    height: '6px',
                    width: '35%',
                    background: 'rgba(255, 255, 255, 0.2)',
                    borderRadius: '4px',
                    marginTop: '4px',
                  }}
                />
              </div>
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '8px', marginTop: '4px' }}>
              <div style={{ height: '28px', background: 'rgba(35, 136, 255, 0.15)', borderRadius: '6px' }} />
              <div style={{ height: '28px', background: 'rgba(123, 77, 255, 0.15)', borderRadius: '6px' }} />
            </div>
          </div>
        )}

        <div className="project-overlay">
          <button
            type="button"
            onClick={handleSelect}
            className="btn btn-primary"
            style={{ padding: '10px 20px', fontSize: '14px' }}
          >
            <span>Ver detalhes do projeto</span>
            <ExternalLink size={16} />
          </button>
        </div>
      </div>

      <div
        style={{
          padding: '24px 24px 28px',
          display: 'flex',
          flexDirection: 'column',
          flex: 1,
          justifyContent: 'space-between',
        }}
      >
        <div>
          <span
            style={{
              fontSize: '12px',
              fontWeight: 600,
              color: 'var(--accent-cyan)',
              textTransform: 'uppercase',
              letterSpacing: '0.08em',
              display: 'block',
              marginBottom: '8px',
            }}
          >
            {project.category}
          </span>

          <h3 style={{ fontSize: '20px', fontWeight: 700, marginBottom: '10px', color: '#F5F7FA' }}>
            {project.title}
          </h3>

          <p
            style={{
              fontSize: '14px',
              color: 'var(--text-gray)',
              lineHeight: 1.5,
              marginBottom: '20px',
            }}
          >
            {description}
          </p>
        </div>

        <div>
          {technologies.length > 0 && (
            <div style={{ display: 'flex', flexWrap: 'wrap', gap: '8px', marginBottom: '20px' }}>
              {technologies.map((tech) => (
                <span
                  key={tech}
                  style={{
                    fontSize: '11px',
                    fontWeight: 600,
                    padding: '4px 10px',
                    borderRadius: '8px',
                    background: 'rgba(41, 50, 71, 0.6)',
                    color: '#F5F7FA',
                    border: '1px solid rgba(41, 50, 71, 0.9)',
                  }}
                >
                  {tech}
                </span>
              ))}
            </div>
          )}

          <button
            type="button"
            onClick={handleSelect}
            className="btn btn-secondary"
            style={{ width: '100%', padding: '10px', fontSize: '13px' }}
          >
            <span>Ver projeto</span>
            <ArrowRight size={14} />
          </button>
        </div>
      </div>
    </div>
  );
};
