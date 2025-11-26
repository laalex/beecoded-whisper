import { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/common/Card';
import Button from '@/components/common/Button';
import { Save, Edit2 } from 'lucide-react';

interface LeadNotesProps {
  notes: string | null;
  onSave: (notes: string) => void;
  isSaving?: boolean;
}

export function LeadNotes({ notes, onSave, isSaving }: LeadNotesProps) {
  const [isEditing, setIsEditing] = useState(false);
  const [editedNotes, setEditedNotes] = useState(notes || '');

  const handleSave = () => {
    onSave(editedNotes);
    setIsEditing(false);
  };

  const handleCancel = () => {
    setEditedNotes(notes || '');
    setIsEditing(false);
  };

  return (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between">
        <CardTitle>Notes</CardTitle>
        {!isEditing && (
          <Button
            variant="ghost"
            size="sm"
            onClick={() => setIsEditing(true)}
          >
            <Edit2 className="w-4 h-4" />
          </Button>
        )}
      </CardHeader>
      <CardContent>
        {isEditing ? (
          <div className="space-y-3">
            <textarea
              value={editedNotes}
              onChange={(e) => setEditedNotes(e.target.value)}
              className="w-full h-32 px-3 py-2 border border-border rounded-lg bg-background text-text focus:outline-none focus:ring-2 focus:ring-primary resize-none"
              placeholder="Add notes about this lead..."
            />
            <div className="flex justify-end gap-2">
              <Button
                variant="ghost"
                size="sm"
                onClick={handleCancel}
                disabled={isSaving}
              >
                Cancel
              </Button>
              <Button
                size="sm"
                onClick={handleSave}
                disabled={isSaving}
              >
                <Save className="w-4 h-4 mr-2" />
                {isSaving ? 'Saving...' : 'Save'}
              </Button>
            </div>
          </div>
        ) : notes ? (
          <p className="text-text whitespace-pre-wrap">{notes}</p>
        ) : (
          <p className="text-text-secondary text-sm">
            No notes yet. Click edit to add notes.
          </p>
        )}
      </CardContent>
    </Card>
  );
}
