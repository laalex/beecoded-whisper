import { useState } from 'react';
import Button from '@/components/common/Button';
import Input from '@/components/common/Input';
import type { InteractionType } from '@/types';
import { X } from 'lucide-react';

interface AddInteractionModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSubmit: (data: {
    type: InteractionType;
    content: string;
    subject?: string;
    direction?: 'inbound' | 'outbound';
  }) => void;
  initialType?: InteractionType;
  isSubmitting?: boolean;
}

export function AddInteractionModal({
  isOpen,
  onClose,
  onSubmit,
  initialType = 'note',
  isSubmitting,
}: AddInteractionModalProps) {
  const [type, setType] = useState<InteractionType>(initialType);
  const [subject, setSubject] = useState('');
  const [content, setContent] = useState('');
  const [direction, setDirection] = useState<'inbound' | 'outbound'>('outbound');

  if (!isOpen) return null;

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!content.trim()) return;

    onSubmit({
      type,
      content: content.trim(),
      subject: subject.trim() || undefined,
      direction: type !== 'note' ? direction : undefined,
    });

    setSubject('');
    setContent('');
  };

  const showDirection = type !== 'note' && type !== 'task';

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center">
      <div
        className="absolute inset-0 bg-black/50"
        onClick={onClose}
      />
      <div className="relative bg-background rounded-xl shadow-xl w-full max-w-lg mx-4 p-6">
        <div className="flex items-center justify-between mb-6">
          <h2 className="text-xl font-semibold text-text">Log Interaction</h2>
          <button
            onClick={onClose}
            className="p-1 hover:bg-surface rounded-lg transition-colors"
          >
            <X className="w-5 h-5 text-text-secondary" />
          </button>
        </div>

        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-text mb-2">
              Type
            </label>
            <select
              value={type}
              onChange={(e) => setType(e.target.value as InteractionType)}
              className="w-full px-3 py-2 border border-border rounded-lg bg-background text-text focus:outline-none focus:ring-2 focus:ring-primary"
            >
              <option value="call">Call</option>
              <option value="email">Email</option>
              <option value="meeting">Meeting</option>
              <option value="note">Note</option>
              <option value="sms">SMS</option>
              <option value="linkedin">LinkedIn</option>
            </select>
          </div>

          {showDirection && (
            <div>
              <label className="block text-sm font-medium text-text mb-2">
                Direction
              </label>
              <div className="flex gap-4">
                <label className="flex items-center">
                  <input
                    type="radio"
                    name="direction"
                    value="outbound"
                    checked={direction === 'outbound'}
                    onChange={() => setDirection('outbound')}
                    className="mr-2"
                  />
                  <span className="text-text">Outbound</span>
                </label>
                <label className="flex items-center">
                  <input
                    type="radio"
                    name="direction"
                    value="inbound"
                    checked={direction === 'inbound'}
                    onChange={() => setDirection('inbound')}
                    className="mr-2"
                  />
                  <span className="text-text">Inbound</span>
                </label>
              </div>
            </div>
          )}

          <Input
            label="Subject (optional)"
            value={subject}
            onChange={(e) => setSubject(e.target.value)}
            placeholder="Brief subject line"
          />

          <div>
            <label className="block text-sm font-medium text-text mb-2">
              Details
            </label>
            <textarea
              value={content}
              onChange={(e) => setContent(e.target.value)}
              className="w-full h-32 px-3 py-2 border border-border rounded-lg bg-background text-text focus:outline-none focus:ring-2 focus:ring-primary resize-none"
              placeholder="Describe the interaction..."
              required
            />
          </div>

          <div className="flex justify-end gap-3 pt-4">
            <Button
              type="button"
              variant="ghost"
              onClick={onClose}
              disabled={isSubmitting}
            >
              Cancel
            </Button>
            <Button type="submit" disabled={isSubmitting || !content.trim()}>
              {isSubmitting ? 'Saving...' : 'Save Interaction'}
            </Button>
          </div>
        </form>
      </div>
    </div>
  );
}
