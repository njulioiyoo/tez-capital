// Simple toast function for now
export const toast = ({ title, description, variant = 'default' }: {
  title: string;
  description?: string;
  variant?: 'default' | 'destructive';
}) => {
  // Simple alert for now - you can replace this with a proper toast implementation
  if (variant === 'destructive') {
    alert(`Error: ${title}${description ? `\n${description}` : ''}`);
  } else {
    alert(`${title}${description ? `\n${description}` : ''}`);
  }
};