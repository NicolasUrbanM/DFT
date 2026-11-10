import React from 'react';
import { FolderGit2, Sparkles, ArrowRight } from 'lucide-react';
import type { PublicProject } from '../services/contentService';
import { ProjectCard } from './ProjectCard';

export interface ProjectsPresentationProps {
  projects?: PublicProject[];
  onSelectProject: (title: string, desc: string, tags: string[]) => void;
  emptyTitle?: string;
  emptyMessage?: string;
  emptyActionText?: string;
  onEmptyAction?: () => void;
  isVisible?: boolean;
  className?: string;
  style?: React.CSSProperties;
}

export const ProjectsPresentation: React.FC<ProjectsPresentationProps> = ({
  projects,
  onSelectProject,
  emptyTitle = 'Nenhum projeto publicado no momento',
  emptyMessage = 'Nossa equipe está trabalhando em novos cases de sucesso e soluções inovadoras. Em breve novos projetos estarão disponíveis aqui!',
  emptyActionText = 'Iniciar um projeto conosco',
  onEmptyAction,
  isVisible = true,
  className = '',
  style,
}) => {
  const hasProjects = Array.isArray(projects) && projects.length > 0;

  if (!hasProjects) {
    return (
      <div
        className={`glass-card reveal-item ${isVisible ? 'reveal-visible' : ''} ${className}`}
        style={{
          padding: '60px 30px',
          textAlign: 'center',
          maxWidth: '680px',
          margin: '0 auto',
          display: 'flex',
          flexDirection: 'column',
          alignItems: 'center',
          gap: '20px',
          background: 'rgba(16, 21, 36, 0.75)',
          border: '1px dashed rgba(40, 215, 229, 0.3)',
          borderRadius: '20px',
          ...style,
        }}
      >
        <div
          style={{
            width: '72px',
            height: '72px',
            borderRadius: '50%',
            background: 'linear-gradient(135deg, rgba(35, 136, 255, 0.2) 0%, rgba(40, 215, 229, 0.2) 100%)',
            border: '1px solid rgba(40, 215, 229, 0.4)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            boxShadow: '0 0 24px rgba(40, 215, 229, 0.25)',
          }}
        >
          <FolderGit2 size={34} color="var(--accent-cyan)" />
        </div>

        <div>
          <h3
            style={{
              fontSize: '22px',
              fontWeight: 700,
              color: '#F5F7FA',
              marginBottom: '10px',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              gap: '8px',
            }}
          >
            <span>{emptyTitle}</span>
            <Sparkles size={18} color="var(--accent-cyan)" />
          </h3>
          <p
            style={{
              fontSize: '15px',
              color: 'var(--text-gray)',
              lineHeight: 1.6,
              maxWidth: '520px',
              margin: '0 auto',
            }}
          >
            {emptyMessage}
          </p>
        </div>

        {emptyActionText && (
          <button
            type="button"
            onClick={
              onEmptyAction ||
              (() => {
                const elem = document.getElementById('contato');
                if (elem) elem.scrollIntoView({ behavior: 'smooth' });
              })
            }
            className="btn btn-primary"
            style={{ marginTop: '10px', padding: '12px 24px', fontSize: '14px' }}
          >
            <span>{emptyActionText}</span>
            <ArrowRight size={16} />
          </button>
        )}
      </div>
    );
  }

  return (
    <div
      className={`projects-grid ${className}`}
      style={{
        display: 'grid',
        gridTemplateColumns: 'repeat(auto-fit, minmax(320px, 1fr))',
        gap: '30px',
        ...style,
      }}
    >
      {projects.map((project, index) => (
        <ProjectCard
          key={project.id || `${project.slug}-${index}`}
          project={project}
          onSelectProject={onSelectProject}
          index={index}
          isVisible={isVisible}
        />
      ))}
    </div>
  );
};

export const ProjectGrid = ProjectsPresentation;
